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

namespace Foomo\Cache\Reflection;

/**
 * internal class to describe a cache resources data
 * 
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 * @internal
 */
class CacheResourceReflection {

	/**
	 * @var string
	 */
	public $resourceName;
	/**
	 * @var boolean
	 */
	public $isStatic;
	/**
	 * @var string
	 */
	public $sourceClassName;
	/**
	 * @var string
	 */
	public $sourceMethodName;
	/**
	 * @var \Foomo\Cache\CacheResourceDescription
	 */
	public $description;
	/**
	 * @var \Foomo\Cache\Reflection\CacheResourceReflectionParameter[]
	 */
	public $parameters = array();

	/**
	 *
	 * @param mixed $classOrObject
	 * @param string $method
	 *
	 * @return \Foomo\Cache\Reflection\CacheResourceReflection
	 */
	public static function getReflection($classOrObject, $method)
	{
		$className = \is_object($classOrObject) ? get_class($classOrObject) : $classOrObject;
		$resourceNameA = $className . '->' . $method;
		$resourceNameB = $className . '::' . $method;
		$directory = \Foomo\Cache\DependencyModel::getInstance()->getDirectory();
		if (isset($directory[$resourceNameA])) {
			return $directory[$resourceNameA];
		} else if (isset($directory[$resourceNameB])) {
			return $directory[$resourceNameB];
		} else {
			return null;
		}
	}

	/**
	 * used by the dependency model
	 *
	 * @internal
	 *
	 * @param mixed $classOrObject
	 * @param string $method
	 *
	 * @return \Foomo\Cache\Reflection\CacheResourceReflection
	 */
	public static function internalGetReflection($classOrObject, $method)
	{
		$classReflection = new \ReflectionClass($classOrObject);
		$methodReflection = new \ReflectionAnnotatedMethod($classReflection->getName(), $method);
		/* @var $cacheableAnnotation \Foomo\Cache\CacheResourceDescription */
		$cacheableAnnotation = $methodReflection->getAnnotation('Foomo\Cache\CacheResourceDescription');
		if ($cacheableAnnotation) {
			$ret = new self;
			$ret->sourceClassName = $ret->resourceName = $classReflection->getName();
			//$ret->invalidationPolicy = self::retrieveInvalidationPolicy($cacheableAnnotation->invalidationPolicy);
			$ret->description = $cacheableAnnotation;

			if ($methodReflection->isStatic()) {
				$ret->isStatic = true;
				$ret->resourceName .= '::';
			} else {
				$ret->isStatic = false;
				$ret->resourceName .= '->';
			}
			$ret->sourceMethodName = $methodReflection->getName();
			$ret->resourceName .= $ret->sourceMethodName;
			foreach ($methodReflection->getParameters() as $paramRefl) {
				$ret->parameters[] = CacheResourceReflectionParameter::getReflection($methodReflection, $paramRefl);
			}
			return $ret;
		} else {
			// this needs to be cached as well - other than that services will be slow
			return null;
		}
	}
}