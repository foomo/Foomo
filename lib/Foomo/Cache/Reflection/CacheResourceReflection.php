<?php

/*
 * bestbytes-copyright-placeholder
 */

namespace Foomo\Cache\Reflection;

use Foomo\Cache\Invalidator;

/**
 * internal class to describe a cache resources data
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
	 * @var Foomo\Cache\CacheResourceDescription
	 */
	public $description;
	/**
	 * @var Foomo\Cache\Reflection\CacheResourceReflectionParameter[]
	 */
	public $parameters = array();

	/**
	 *
	 * @param mixed $classOrObject
	 * @param string $method
	 * 
	 * @return Foomo\Cache\Reflection\CacheResourceReflection
	 */
	public static function getReflection($classOrObject, $method)
	{
		static $runTimeCache = array();
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
	 * used by the dependencymodel
	 * 
	 * @internal
	 * 
	 * @param mixed $classOrObject
	 * @param string $method
	 * 
	 * @return Foomo\Cache\Reflection\CacheResourceReflection
	 */
	public static function internalGetReflection($classOrObject, $method)
	{
		$classRefl = new \ReflectionClass($classOrObject);
		$methodRefl = new \ReflectionAnnotatedMethod($classRefl->getName(), $method);
		// @var $cacheableAnnotation \Foomo\Cache\CacheResourceDescription 
		$cacheableAnnotation = $methodRefl->getAnnotation('Foomo\Cache\CacheResourceDescription');
		if ($cacheableAnnotation) {
			$ret = new self;
			$ret->sourceClassName = $ret->resourceName = $classRefl->getName();
			//$ret->invalidationPolicy = self::retrieveInvalidationPolicy($cacheableAnnotation->invalidationPolicy);
			$ret->description = $cacheableAnnotation;

			if ($methodRefl->isStatic()) {
				$ret->isStatic = true;
				$ret->resourceName .= '::';
			} else {
				$ret->isStatic = false;
				$ret->resourceName .= '->';
			}
			$ret->sourceMethodName = $methodRefl->getName();
			$ret->resourceName .= $ret->sourceMethodName;
			foreach ($methodRefl->getParameters() as $paramRefl) {
				$ret->parameters[] = CacheResourceReflectionParameter::getReflection($methodRefl, $paramRefl);
			}
			return $ret;
		} else {
			// this needs to be cached as well - other than that services will be slow
			return null;
		}
	}
}