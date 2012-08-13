<?php

/*
 * This file is part of the foomo Opensource Framework.
 *
 * The foomo Opensource Framework is free software: you can redistribute it
 * and/or modify it under the terms of the GNU Lesser General Public License as
 * published  by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * The foomo Opensource Framework is distributed in the hope that it will
 * be useful, but WITHOUT ANY WARRANTY; without even the implied warranty
 * of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License along with
 * the foomo Opensource Framework. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Foomo\Cache;

use Foomo\Cache\Persistence\Expr;

/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
class Manager {
	const CONF_SEPARATOR= '::';

	/**
	 * fast persistence
	 *
	 * @var Foomo\Cache\Persistence\FastPersistorInterface
	 */
	private static $fastPersistor;
	/**
	 * queryable persistence
	 *
	 * @var Foomo\Cache\Persistence\QueryablePersistorInterface
	 */
	private static $queryablePersistor;
	/**
	 * depending objects invalidator
	 * @var Foomo\Cache\Invalidator
	 */
	private static $invalidator;

	/**
	 * (re)initialize method caching
	 *
	 * @param Foomo\Cache\Persistence\FastPersistorInterface $fastPersistor
	 * @param Foomo\Cache\Persistence\QueryablePersistorInterface $queryablePersistor
	 */
	public static function initialize(Persistence\QueryablePersistorInterface $queryablePersistor, Persistence\FastPersistorInterface $fastPersistor = null)
	{
		self::$queryablePersistor = $queryablePersistor;
		self::$fastPersistor = $fastPersistor;
		self::$invalidator = new Invalidator();
	}

	/**
	 * read the cache config from environment variables and bootstap
	 */
	public static function bootstrap()
	{
		if (isset($_SERVER['FOOMO_CACHE_QUERYABLE'])) {
			$queryablePersistor = self::getPersistorFromConf($_SERVER['FOOMO_CACHE_QUERYABLE'], true);
		}
		if (isset($_SERVER['FOOMO_CACHE_FAST'])) {
			$fastPersistor = self::getPersistorFromConf($_SERVER['FOOMO_CACHE_FAST'], false);
		} else {
			$fastPersistor = null;
		}
		if (!$queryablePersistor) {
			trigger_error('could not load or initialize a queryable cache persistor ' . (isset($_SERVER['FOOMO_CACHE_QUERYABLE'])) ? ' - config "' . $_SERVER['FOOMO_CACHE_QUERYABLE'] . '" could not be parsed' : 'config was empty', \E_USER_ERROR);
		}
		self::initialize($queryablePersistor, $fastPersistor);
	}

	/**
	 * gets the persistor based on supplied config from the apache vhosts.conf file
	 *
	 * @param string $confString
	 * @param boolean $queryable
	 *
	 * @return mixed persistorClass
	 */
	public static function getPersistorFromConf($confString, $queryable)
	{
		if (!empty($confString)) {
			$firstSeparatorOccurence = strpos($confString, self::CONF_SEPARATOR);
			if ($firstSeparatorOccurence !== false) {
				// there is a conf
				$persistorClassIdentifier = substr($confString, 0, $firstSeparatorOccurence);
				$conf = \substr($confString, $firstSeparatorOccurence + strlen(self::CONF_SEPARATOR));
			} else {
				// no conf
				$persistorClassIdentifier = $confString;
				$conf = '';
			}
			switch (\strtolower($persistorClassIdentifier)) {
				case 'apc':
					$persistorPrefix = 'APC';
					break;
				case 'pdo':
					$persistorPrefix = 'PDO';
					break;
				case 'apc':
					$persistorPrefix = 'APC';
				default:
					$persistorPrefix = ucfirst($persistorClassIdentifier);
			}
			$persistorClass = __NAMESPACE__ . '\\Persistence\\' . ($queryable ? 'Queryable' : 'Fast') . '\\' . $persistorPrefix . 'Persistor';
			return new $persistorClass($conf);
		}
	}

	/**
	 * @return Foomo\Cache\Persistence\FastPersistorInterface
	 */
	public static function getFastPersistor()
	{
		return self::$fastPersistor;
	}

	/**
	 * @return Foomo\Cache\Persistence\QueryablePersistorInterface
	 */
	public static function getQueryablePersistor()
	{
		return self::$queryablePersistor;
	}

	/**
	 * loads a cache resource from the cache
	 *
	 * @param CacheResource $resource a (possibly empty) resource with the id set
	 *
	 * @param boolean $countHits
	 *
	 * @return Foomo\Cache\CacheResource
	 */
	public static function load(CacheResource $resource, $countHits = false)
	{
		try {
			// try fast persistor
			$ret = null;
			$fromFastCache = true;
			if (isset(self::$fastPersistor) && ($cachedResource = self::getFastPersistor()->load($resource))) {
				$ret = $cachedResource;
				$fromFastCache = true;
			} else {
				// fallback to queryable persistor
				$ret = self::getQueryablePersistor()->load($resource, $countHits);
				$fromFastCache = false;
			}

			if (self::checkCachedResult($ret, $fromFastCache)) {
				// take care, that the resource is put back to the fast cache
				// a nice example for this is a restart of memcached
				if (!$fromFastCache && isset(self::$fastPersistor)) {
					$fastPersistor = self::getFastPersistor();
					$fastPersistor->save($ret);
				}
				return $ret;
			} else {
				return null;
			}
		} catch (\Exception $e) {
			\trigger_error(__CLASS__ . __METHOD__ . $e->getMessage());
		}
	}

	/**
	 * saves a resource into the cache
	 *
	 * @param CacheResource $resource
	 * @return boolean success
	 */
	public static function save(CacheResource $resource)
	{
		$fastSuccess = true; // not necessary the fast persistor is always there
		$queryableSuccess = false;
		try {
			$queryableSuccess = self::getQueryablePersistor()->save($resource);

			if (isset(self::$fastPersistor) && $queryableSuccess === true) {
				$fastSuccess = self::getFastPersistor()->save($resource);
			}
			$invalidationPolicy = $resource->invalidationPolicy;

			$success = ($fastSuccess && $queryableSuccess);

			if (($invalidationPolicy != Invalidator::POLICY_DO_NOTHING) && $success === true) {
				Manager::invalidate($resource);
			}
			return $success;
		} catch (\Exception $e) {
			\trigger_error(__CLASS__ . __METHOD__ . $e->getMessage());
		}
	}

	/**
	 * deletes a resource from the cache
	 *
	 * @param CacheResource $resource
	 *
	 * @return boolean true if deleted
	 */
	public static function delete(CacheResource $resource)
	{
		try {
			$qSuccess = self::getQueryablePersistor()->delete($resource);
			$fSuccess = true;
			if (isset(self::$fastPersistor)) {
				$fSuccess = self::getFastPersistor()->delete($resource);
			}
			if ($resource->invalidationPolicy == Invalidator::POLICY_DO_NOTHING) {
				\trigger_error(__CLASS__ . __METHOD__ . 'delete called with POLICY_DO_NOTHING: this results in an unconsistent cache state.');
			}
			$success = $qSuccess && $fSuccess;

			if ($success === true) {
				Manager::invalidate($resource);
			}
			return $success;
		} catch (\Exception $e) {
			\trigger_error(__CLASS__ . __METHOD__ . $e->getMessage());
			return false;
		}
	}

	/**
	 * Check if resource is in the fast cache
	 *
	 * @param Foomo\Cache\CacheResource $resource
	 */
	public static function isResourceInFastCache($resource)
	{
		if (isset(self::$fastPersistor)) {
			$cachedResource = self::getFastPersistor()->load($resource);
			if ($cachedResource != null) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * finds all resources matching expression
	 *
	 * @param string $resourceName
	 * @param Foomo\Cache\Persistence\Expr $expr
	 * @param integer $limit
	 * @param integer $offset
	 *
	 * @return \Foomo\Cache\Persistence\CacheResourceIterator
	 */
	public static function query($resourceName, Expr $expr = null, $limit = 0, $offset = 0)
	{
		//hack to allow default expression
		if (!$expr) {
			$expr = Expr::idNe('not an id');
		}
		return self::getQueryablePersistor()->query($resourceName, $expr, $limit, $offset);
	}

	/**
	 * invalidates resources in cache that depend on resource argument. root resource itself is not invalidated
	 * @param CacheResource $resource, contains invalidationPolicy
	 */
	public static function invalidate(CacheResource $resource, $invalidateRoot = false, $invalidationPolicy = null, $verbose = false)
	{
		if ($invalidationPolicy != null)
			$resource->invalidationPolicy = $invalidationPolicy;
		self::$invalidator->invalidate($resource, $invalidateRoot, $verbose);
	}

	/**
	 * invalidate resources matching query
	 *
	 * @param string $resourceName
	 *
	 * @param Expr $expr if null matches all resources with supplied name
	 *
	 * @param boolean $invalidateRoot default false
	 *
	 * @param string $invalidationPolicy default null, meaning it will be taken from the annotation
	 */
	public static function invalidateWithQuery($resourceName, Expr $expr = null, $invalidateRoot = false, $invalidationPolicy = null)
	{
		foreach (self::query($resourceName, $expr) as $resource) {
			if (isset($invalidationPolicy))
				$resource->invalidationPolicy = $invalidationPolicy;
			self::invalidate($resource, $invalidateRoot, $invalidationPolicy, false);
		}
	}

	/**
	 * clear all. if resource name provided drops only entires for resource
	 *
	 * @param string $resourceName
	 *
	 * @param boolean $recreateStructures
	 *
	 */
	public static function reset($resourceName = null, $recreateStructures = true)
	{
		self::getQueryablePersistor()->reset($resourceName, $recreateStructures);
		if (null != ($fastPersistor = self::getFastPersistor())) {
			$fastPersistor->reset();
		}
	}

	/**
	 * returns an array of resource ids of all resources
	 *
	 * @param string $resourceName if null list all, else only resources matching name
	 */
	public static function getListOfCachedResources($resourceName = null)
	{
		return self::getQueryablePersistor()->getListOfCachedResources($resourceName);
	}

	/**
	 * check the cached result.
	 *
	 * @param Foomo\Cache\CacheResource $resource
	 *
	 * @param bool $fromFastCache
	 *
	 * @return true if valid and not expired, otherwise false
	 */
	public static function checkCachedResult($resource, $fromFastCache = false)
	{
		if (\is_null($resource))
			return false;
		if ($resource->status == CacheResource::STATUS_INVALID)
			return false;
		$expirationTime = 0;
		if ($fromFastCache) {
			$expirationTime = $resource->expirationTimeFast;
		} else {
			$expirationTime = $resource->expirationTime;
		}
		if ($expirationTime == 0) {
			return true;
		} else {
			if (\time() < $expirationTime)
				return true;
			else
				return false; //expired
		}
	}

	/**
	 * @todo jan: Refactored this method to work correcty. Pleas validate and add comment.
	 *
	 * @param mixed $classOrObject object/class to call
	 * @param string $method name of the method
	 * @return string
	 */
	public static function getResourceName($classOrObject, $method)
	{
		return \Foomo\Cache\Reflection\CacheResourceReflection::getReflection($classOrObject, $method)->resourceName;
	}

	/**
	 * get the compiled expression interpretation from the queryable persistor
	 *
	 * @param string $resourceName
	 *
	 * @param Foomo\Cache\Persistence\Expr $expression
	 *
	 * @return string
	 */
	public static function getExpressionInterpretation($resourceName, $expression)
	{
		return self::$queryablePersistor->getExpressionInterpretation($resourceName, $expression);
	}

	/**
	 * creates db for cache if not exists
	 */
	public static function createStorageStructure()
	{
		self::getQueryablePersistor()->reset(null);
		if (null != ($fastPersistor = self::getFastPersistor())) {
			$fastPersistor->reset();
		}
	}

	/**
	 * populates the fast cache with stored resources from the queryable cache
	 */
	public static function populateFastCache()
	{
		self::$fastPersistor->reset();
		$namesIterator = self::$queryablePersistor->getCachedResourceNames();
		echo 'Populating fast cache with cached resources from queryable cache' . PHP_EOL;
		foreach ($namesIterator as $resourceName) {
			echo '---------------------------------------------------------------------------------------------------------------' . PHP_EOL;
			echo $resourceName[0] . PHP_EOL;
			$expr = \Foomo\Cache\Persistence\Expr::idNe('we want all resources - this is not an id');
			$resources = Manager::query($resourceName[0], $expr);
			foreach ($resources as $resource) {
				echo '------------>' . $resource->name . ' - with id: ' . $resource->id . PHP_EOL;
				@ob_flush();
				flush();
				self::$fastPersistor->save($resource);
			}
		}
	}

	public static function validateStorageStructure($resourceName)
	{
		self::$queryablePersistor->validateStorageStructure($resourceName, true);
	}

}
