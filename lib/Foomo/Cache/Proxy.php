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

use ReflectionAnnotatedMethod;

/**
 * run your method calls through this class to get cached results
 *
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
class Proxy {

	/**
	 *
	 * @param string $resourceName
	 *
	 * @return Foomo\Cache\CacheResource
	 */
	public static function getEmptyResourceFromResourceName($resourceName)
	{
		// maybe another static cache in here ...
		if (\strpos($resourceName, '::') !== false) {
			$parts = explode('::', $resourceName);
			$classOrObject = $parts[0];
		} else {
			$parts = explode('->', $resourceName);
			$className = $parts[0];
			$classOrObject = self::getClassInstance($className);
		}
		$method = $parts[1];
		return self::getEmptyResource($classOrObject, $method, array());
	}

	/**
	 * will check if the constructor can be called or a singleton is available
	 *
	 * @param string $className
	 * @internal
	 *
	 * @return stdClass
	 */
	public static function getClassInstance($className)
	{
		$inst = null;
		if (\method_exists($className, '__construct')) {
			$constructorRefl = new \ReflectionMethod($className, '__construct');
			if ($constructorRefl->isPrivate()) {
				if (\method_exists($className, 'getInstance')) {
					$inst = \call_user_func_array(array($className, 'getInstance'), array());
				} else {
					trigger_error('you have a private constructor and no getInstance method, sorry i am not smart enough to call you', \E_USER_ERROR);
				}
			}
		}
		if (empty($inst)) {
			$inst = new $className;
		}
		return $inst;
	}

	/**
	 * get an empty call resource
	 *
	 * @param mixed $classOrObject object/class to call
	 * @param string $method name of the method
	 * @param array $arguments arguments for the call
	 *
	 * @deprecated use CacheResourceReflection instead
	 * @internal really ?!
	 *
	 * @return Foomo\Cache\CacheResource
	 */
	public static function getEmptyResource($classOrObject, $method, $arguments)
	{
		// is it really
		// trigger_error('stop using this use CacheResourceReflection instead', \E_USER_DEPRECATED);

        $resource = new CacheResource();
		if (($classOrObject instanceof DependencyModel) && $method == 'cachedGetDirectory') {

			$cacheResourceRefl = new Reflection\CacheResourceReflection;
			$cacheResourceRefl->sourceClassName = 'Foomo\\Cache\\DependencyModel';
			$cacheResourceRefl->sourceMethodName = 'cachedGetDirectory';
			$cacheResourceRefl->resourceName = 'Foomo\\Cache\\DependencyModel->cachedGetDirectory';
			$cacheResourceRefl->description = new CacheResourceDescription(array(
						'dependencies' => array('Foomo\AutoLoader::cachedGetClassMap')
					));
			$cacheResourceRefl->parameters = array();
		} else {
			$cacheResourceRefl = Reflection\CacheResourceReflection::getReflection($classOrObject, $method);
    	}
		if ($cacheResourceRefl) {
    		$resource->invalidationPolicy = $cacheResourceRefl->description->invalidationPolicy;
			$resource->name = $cacheResourceRefl->resourceName;
			$resource->sourceClass = $cacheResourceRefl->sourceClassName;
			$resource->sourceMethod = $cacheResourceRefl->sourceMethodName;
			$resource->sourceStatic = $cacheResourceRefl->isStatic;


			$resource->id = self::getId($resource->name, $arguments);
			$resource->hits = 0;
			$resource->creationTime = \time();
			$resource->debugCreationTime = \microtime(true);


			$argCounter = 0;
			foreach ($cacheResourceRefl->parameters as $paramRefl) {
				/* @var $paramRefl Foomo\Cache\Reflection\CacheResourceReflectionParameter */
				if (count($arguments) > $argCounter) {
					if ($paramRefl->type != 'mixed' && !is_null($arguments[$argCounter])) {
						// !mixed && !null needs to be checked
						if (\is_integer($arguments[$argCounter])) {
							if ($paramRefl->type != 'integer') {
								\trigger_error(__CLASS__ . __METHOD__ . ": argument " . $paramRefl->name . " of " . $resource->sourceClass . "/" . $resource->sourceMethod . " MUST NOT BE integer! " . $paramRefl->type . " expected");
							}
						} else if (\is_bool($arguments[$argCounter])) {
							if ($paramRefl->type != 'boolean') {
								\trigger_error(__CLASS__ . __METHOD__ . ": argument " . $paramRefl->name . " of " . $resource->sourceClass . "/" . $resource->sourceMethod . " MUST NOT BE boolean! " . $paramRefl->type . " expected");
							}
						} else if (\is_array($arguments[$argCounter])) {
							if ($paramRefl->type != 'array' && \strpos($paramRefl->type, '[]') === false) {
								\trigger_error(__CLASS__ . __METHOD__ . ": argument " . $paramRefl->name . " of " . $resource->sourceClass . "/" . $resource->sourceMethod . " MUST NOT BE array! " . $paramRefl->type . " expected");
							}
						} else if (\is_double($arguments[$argCounter])) {
							if ($paramRefl->type != 'double') {
								\trigger_error(__CLASS__ . __METHOD__ . ": argument " . $paramRefl->name . " of " . $resource->sourceClass . "/" . $resource->sourceMethod . " MUST NOT BE double! " . $paramRefl->type . " expected");
							}
						} else if (\is_float($arguments[$argCounter])) {
							if ($paramRefl->type != 'float') {
								\trigger_error(__CLASS__ . __METHOD__ . ": argument " . $paramRefl->name . " of " . $resource->sourceClass . "/" . $resource->sourceMethod . " MUST NOT BE double! " . $paramRefl->type . " expected");
							}
						} else if (\is_long($arguments[$argCounter])) {
							if ($paramRefl->type != 'long') {
								\trigger_error(__CLASS__ . __METHOD__ . ": argument " . $paramRefl->name . " of " . $resource->sourceClass . "/" . $resource->sourceMethod . " MUST NOT BE long! " . $paramRefl->type . " expected");
							}
						} else if (\is_string($arguments[$argCounter])) {
							if ($paramRefl->type != 'string') {
								\trigger_error(__CLASS__ . __METHOD__ . ": argument " . $paramRefl->name . " of " . $resource->sourceClass . "/" . $resource->sourceMethod . " MUST NOT BE string! " . $paramRefl->type . " expected");
							}
						} else {
							//object
							if (!($arguments[$argCounter] instanceof $paramRefl->type)) {
								\trigger_error(__CLASS__ . __METHOD__ . ": argument " . $paramRefl->name . " of " . $resource->sourceClass . "/" . $resource->sourceMethod . " WRONG! " . $paramRefl->type . " expected");
							}
						}
					}
					$resource->properties[$paramRefl->name] = $arguments[$argCounter];

				}
                $resource->propertyTypes[$paramRefl->name] = $paramRefl->type;
				$argCounter++;
			}
            $resource->status = CacheResource::STATUS_VALID;
            //set the expiration time for the resource
            if (($classOrObject instanceof DependencyModel) && $method == 'cachedGetDirectory') {
               $resource->expirationTime = 0;
               $resource->expirationTimeFast = 0;
            } else {
                CacheResource::setResourceExpiration($resource);
            }
            return $resource;
		} else {
			return null;
		}
	}

	/**
	 * computes an id for the resource with arguments / properties
	 *
	 * @param string $resourceName
	 * @param array $arguments
	 *
	 * @return string
	 */
	public static function getId($resourceName, $arguments)
	{
		$idStr = $resourceName;
		foreach ($arguments as $argument) {
			$idStr .= self::getDataFingerprint($argument);
		}
		return \md5($idStr);
	}

	/**
	 * compute a fingerpring of an object. for object that implements __toString returns md5 of its return value
	 * otherwise returns md5 of serialized object, which may be  slower for large objects
	 *
	 * @param mixed $data
	 *
	 * @return string
	 */
	public static function getDataFingerprint($data)
	{
		if (is_object($data) && !is_callable(array($data, '__toString')) || !is_scalar($data)) {
			return serialize($data);
		} else {
			return (string) $data;
		}
	}

	/**
	 * calls a method using the cache
	 *
	 * the default invalidation policy is infered from the annotation or overriden
	 * by the parameter $invalidationPolicy if not null
	 *
	 * @param mixed $classOrObject class name or object on which the method is called
	 * @param string $method method name
	 * @param array $arguments non asociative array of method argument values
	 * @param string $invalidationPolicy  Invalidator::POLICY_INSTANT_REBUILD | ...
	 * @param integer $expirationTime  cache expiration time in ms
	 * @param integer $expirationTimeFast  fast cache expiration time in ms
	 *
	 * @return mixed
	 */
	public static function call($classOrObject, $method, $arguments = array(), $invalidationPolicy=null, $expirationTime=null, $expirationTimeFast=null)
	{
		if (\Foomo\AutoLoader::getClassMapAvailable()) {
			//get a resource without value property. resource includes invalidation policy from annotation
			//or default value Invalidator::POLICY_INSTANT_REBUILD
			$resource = self::getEmptyResource($classOrObject, $method, $arguments);
			if (!is_null($resource)) {
				# if supplied invalidation policy then override value from annotation
				if (isset($invalidationPolicy)) $resource->invalidationPolicy = $invalidationPolicy;
				# if supplied expiration time then override value from annotation
				if (isset($expirationTime)) $resource->expirationTime = $expirationTime;
				# if supplied expiration time fast then override value from annotation
				if (isset($expirationTimeFast)) $resource->expirationTimeFast = $expirationTimeFast;

				$cachedResult = Manager::load($resource);
				if ($cachedResult) {
					return $cachedResult->value;
				} else {
					$resource->value = \call_user_func_array(array($classOrObject, $method), $arguments);
					Manager::save($resource);
					return $resource->value;
				}
			}
		}
		// no class map or no annotation
		return \call_user_func_array(array($classOrObject, $method), $arguments);
	}

}
