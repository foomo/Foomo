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

/**
 * Invalidates a set of resources linked into a dependency tree
 * 
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
class Invalidator {
	const POLICY_INSTANT_REBUILD = 'POLICY_INSTANT_REBUILD';
	const POLICY_INVALIDATE = 'POLICY_INVALIDATE';
	const POLICY_DELETE = 'POLICY_DELETE';
	const POLICY_DO_NOTHING = 'POLICY_DO_NOTHING';

	private $dependencyModel;

	public function __construct()
	{
		$this->dependencyModel = DependencyModel::getInstance();
	}

	/**
	 * invalidate a tree of dependent resources using invalidation policy
	 *
	 * the used invalidation policy is taken from the reource invalidationPolicy variable
	 *
	 * @param Foomo\Cache\CacheResource $resource
	 * @param bool $invalidateRoot if true, cache for $resource will be invalidated, ese only child leafs
	 * @param bool $verbose if true will output to progress
	 *
	 * @return int number of invalidated objects
	 */
	public function invalidate(CacheResource $resource, $invalidateRoot = false, $verbose = false)
	{
		// \Foomo\Utils::appendToPhpErrorLog(__METHOD__ . ' : parent : ' . $resource->name . ' ' . \json_encode($resource->properties) . PHP_EOL);
		$persistor = Manager::getQueryablePersistor();
		if (!isset($persistor)) {
			\trigger_error(__CLASS__ . __METHOD__ . ' :queryable persitor not set on manager', \E_USER_ERROR);
			throw new \Exception('Queryable persitor not set on Manager.');
		}

		$num = 0;
		$invalidationPolicy = $resource->invalidationPolicy;
		$listOfDependentResourceNames = $this->dependencyModel->getDependencyList($resource->name);

		foreach ($listOfDependentResourceNames as $dependentResourceName) {
			//find all method calls
			//$dependentResources = \Foomo\Cache\Manager::find($dependentResourceName, $resource->properties);

			if (!empty($resource->properties)) {
				$expr = Persistence\Expr::propsEq($resource->properties);
			} else {
				$expr = null;
			}

			$dependentResources = Manager::query($dependentResourceName, $expr);

			foreach ($dependentResources as $dependentResource) {
				//deal with root resource invalidation, skip if not to be invalidated
				if (!$invalidateRoot) {
					if ($dependentResource->id == $resource->id)
						continue;
				}
				switch ($invalidationPolicy) {
					case self::POLICY_INVALIDATE:
						$fSuccess = true;
						$qSuccess = false;

						if ($dependentResource->status != \Foomo\Cache\CacheResource::STATUS_INVALID) {
							$dependentResource->status = \Foomo\Cache\CacheResource::STATUS_INVALID;
							//when invalidating fast cache is deleted
							if (Manager::getFastPersistor()) {
								$fSuccess = Manager::getFastPersistor()->delete($dependentResource);
							}

							$qSuccess = Manager::getQueryablePersistor()->save($dependentResource);


							if ($verbose)
								echo '--> Setting resource status' . $dependentResource->name . ' with id ' . $dependentResource->id . ' to INVALID' . \PHP_EOL;

							if (($fSuccess && $qSuccess) === false) {
								//this might be serious... couldn not save during invalidate. Since this may come at an arbitrary place in the tree
								// the cache consistency might be compromised
								\trigger_error(__METHOD__ . 'Could not SAVE resource during invalidate. ' . $dependentResource->name . ' with id '. $dependentResource->id . '-> queryable success: ' . ($qSuccess?'true':'false') . '-->'. ($fSuccess?'true':'false') . ' CACHE MIGHT BE IN AN INCONSISTENT STATE. Terminating process here.', \E_USER_ERROR);
							}
							$num++;
						}
						break;
					case self::POLICY_INSTANT_REBUILD:
						$classOrObject = "";
						if ($dependentResource->sourceStatic) {
							$classOrObject = $dependentResource->sourceClass;
						} else {
							$classOrObject = Proxy::getClassInstance($dependentResource->sourceClass);
						}
						$dependentResource->value = call_user_func_array(array($classOrObject, $dependentResource->sourceMethod), \array_values($dependentResource->properties));
						$dependentResource->status = CacheResource::STATUS_VALID;
						$dependentResource->creationTime = \time();
						CacheResource::setResourceExpiration($dependentResource);
						$fSuccess = true;
						$qSuccess = false;
						if (Manager::getFastPersistor()) {
							$fSuccess = Manager::getFastPersistor()->save($dependentResource);
						}
						$qSuccess = Manager::getQueryablePersistor()->save($dependentResource);
						if ($verbose)
							echo '--> Rebuilding resource' . $dependentResource->name . ' with id ' . $dependentResource->id . \PHP_EOL;
						if (($fSuccess && $qSuccess) === false) {
							//this might be serious... could not not save during invalidate. Since this may come at an arbitrary place in the tree
							// the cache consistency might be compromised
							\trigger_error(__METHOD__ . 'Could not SAVE resource during invalidate. '.$dependentResource->name . ' with id '. $dependentResource->id . '-> queryable success: ' . ($qSuccess?'true':'false') . '-->'. ($fSuccess?'true':'false') . ' CACHE MIGHT BE IN AN INCONSISTENT STATE. Terminating process here.', \E_USER_ERROR);
						}
						$num++;
						break;
					case self::POLICY_DELETE:
						$fSuccess = true;
						$qSuccess = false;
						if (Manager::getFastPersistor()) {
							$fSuccess = Manager::getFastPersistor()->delete($dependentResource);
						}
						$qSuccess = Manager::getQueryablePersistor()->delete($dependentResource);
						if ($verbose)
							echo '--> Deleting resource' . $dependentResource->name . ' with id ' . $dependentResource->id . \PHP_EOL;
						if (($fSuccess && $qSuccess) === false) {
							//this might be serious... couldn not save during invalidate. Since this may come at an arbitrary place in the tree
							// the cache consistency might be compromised
							\trigger_error(__METHOD__ . 'Could not DELETE resource during invalidate. '.$dependentResource->name . ' with id '. $dependentResource->id . '-> queryable success: ' . ($qSuccess?'true':'false') . '-->'. ($fSuccess?'true':'false') . ' CACHE MIGHT BE IN AN INCONSISTENT STATE. Terminating process here.', \E_USER_ERROR);
						}
						$num++;
						break;
					case self::POLICY_DO_NOTHING:
						if ($verbose)
							echo '--> Leaving resource' . $dependentResource->name . ' with id ' . $dependentResource->id . ' untouched (POLICY_DO_NOTHING)' . \PHP_EOL;
						\trigger_error(__CLASS__ . __METHOD__ . ' DOING nothing. invalidation policy : ' . $invalidationPolicy);
						//$num++;
						break;
					default:
						\trigger_error(__CLASS__ . __METHOD__ . ' Unknown invalidation policy : ' . $invalidationPolicy);
						break;
				}
			}
		}
		return $num;
	}

	/**
	 * returns a list of resources to be invalidated
	 *
	 * the used invalidation policy is taken from the reource invalidationPolicy variable
	 *
	 * @param Foomo\Cache\CacheResource $resource
	 * @param bool $invalidateRoot if true, cache for $resource will be invalidated, ese only child leafs
	 *
	 * @return Foomo\Cache\CacheResource[] list of resources to invalidate
	 */
	public function getInInvalidationList(CacheResource $resource, $invalidateRoot = false)
	{

		$invalidateList = array();
		$persistor = Manager::getQueryablePersistor();
		if (!isset($persistor)) {
			\trigger_error(__CLASS__ . __METHOD__ . ' :queryable persitor not set on manager', \E_USER_ERROR);
			throw new \Exception('Queryable persitor not set on Manager.');
		}

		$num = 0;
		$listOfDependentResourceNames = $this->dependencyModel->getDependencyList($resource->name);

		foreach ($listOfDependentResourceNames as $dependentResourceName) {
			//find all method calls
			//$dependentResources = \Foomo\Cache\Manager::find($dependentResourceName, $resource->properties);
			if (!empty($resource->properties)) {
				$expr = Persistence\Expr::propsEq($resource->properties);
			} else {
				$expr = null;
			}


			$dependentResources = Manager::query($dependentResourceName, $expr);
			foreach ($dependentResources as $dependentResource) {
				//deal with root resource invalidation, skip if not to be invalidated
				if (!$invalidateRoot) {
					if ($dependentResource->id == $resource->id)
						continue;
				}
				$invalidateList[] = $dependentResource;
			}
		}
		return $invalidateList;
	}

}