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
 * A class representing a cacheable resource identifiable by resource name and parameters
 * 
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
class CacheResource {
	const STATUS_INVALID = 0;

	const STATUS_VALID = 1;

	/**
	 * creation time in microseconds
	 *
	 * @var integer
	 */
	public $creationTime;
	/**
	 * timestamp of expiration
	 *
	 * @var integer
	 */
	public $expirationTimeFast;
	/**
	 * timestamp of expiration
	 *
	 * @var integer
	 */
	public $expirationTime;
	/**
	 * status of resource in cache
	 * @var integer
	 */
	public $status;
	/**
	 * number of cache hits for this particular resource
	 */
	public $hits;
	/**
	 * resource object being cached
	 * @var mixed
	 */
	public $value;
	/**
	 * name of resource. __CLASS__.__METHOD__ in case of caching
	 * @var string
	 */
	public $name;
	/**
	 * unique identifier used to retrieve resource
	 *
	 * @var string
	 */
	public $id;
	/**
	 * full name of the generating class/object
	 *
	 * @var string
	 */
	public $sourceClass;
	/**
	 * @var string
	 */
	public $sourceMethod;
	/**
	 * is the resource the result of a static method call or a call on a method\
	 * on an object
	 *
	 * @var bool
	 */
	public $sourceStatic;
	/**
	 * resource parameters. method parameters in case of method call caching
	 * @var array parameterName => parameterValue pairs
	 */
	public $properties = array();
	/**
	 * associative array of propertyName => propertyType
	 *
	 * @var array
	 */
	public $propertyTypes = array();
	/**
	 * invalidation policy
	 * @var int
	 */
	public $invalidationPolicy;
	/**
	 * microtime
	 *
	 * @var float
	 */
	public $debugCreationTime;

	/**
	 * computes an iD for a method call, md5 of...
	 */
	public static function getMethodCallId($classOrObject, $parameters)
	{
		$val = \serialize($classOrObject) . \serialize($parameters);
		return \md5($val);
	}

	/**
	 * set resource expiration based on values from annotation
	 *
	 * @param Foomo\Cache\CacheResource $resource
	 * @param ReflectionClass $classRefl if null its created internally
	 * @param array $info array containing name => CacheResourceDescription pairs, if null it is retrieved internally (slower!)
	 */
	public static function setResourceExpiration($resource)
	{
		$classRefl = new \ReflectionClass($resource->sourceClass);
		$refl = Reflection\CacheResourceReflection::getReflection($resource->sourceClass, $resource->sourceMethod);
		if (!empty($refl)) {
			//set expiration time using annotation value
			if ($refl->description->lifeTime == 0) {
				$resource->expirationTime = 0;
			} else {
				$resource->expirationTime = $refl->description->lifeTime + \time();
			}
			//set expiration time in fast cache using annotation value
			if ($refl->description->lifeTimeFast == 0) {
				$resource->expirationTimeFast = 0;
			} else {
				$resource->expirationTimeFast = $refl->description->lifeTimeFast + \time();
			}
		}
	}

	public function getPropertyDefinitions()
	{
		$ret = array();
		$methodRefl = new \ReflectionMethod($this->sourceClass, $this->sourceMethod);
		/* @var $paramRefl \ReflectionParameter */
		$phpDoc = new \Foomo\Reflection\PhpDocEntry($methodRefl->getDocComment(), $methodRefl->getDeclaringClass()->getNamespaceName());
		foreach ($methodRefl->getParameters() as $paramRefl) {
			$propertyDef = new CacheResourcePropertyDefinition($paramRefl, $phpDoc);
			$ret[$propertyDef->name] = $propertyDef;
		}
		return $ret;
	}

	public function __wakeup()
	{
		if (!isset($this->debugCreationTime)) {
			$this->debugCreationTime = -1;
		}
	}

}