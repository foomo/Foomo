<?php

/*
 * bestbytes-copyright-placeholder
 */

namespace Foomo\Cache\Reflection;

use Foomo\Cache\CacheResourcePropertyDefinition;

class CacheResourceReflectionParameter {

	/**
	 * @var string
	 */
	public $name;
	/**
	 * @var string
	 */
	public $type;
	/**
	 * @var boolean
	 */
	public $optional;

	/**
	 * some reflection please
	 * 
	 * @param \ReflectionParameter $parameterRefl
	 * 
	 * @return self 
	 */
	public static function getReflection(\ReflectionMethod $methodRefl, \ReflectionParameter $parameterRefl)
	{
		$ret = new self;
		$ret->name = $parameterRefl->getName();
		$ret->optional = $parameterRefl->isOptional();
		$ret->type = self::getParamType($ret->name, $methodRefl);
		return $ret;
	}

	/**
	 * returns the type of the parameter, i.e. property as defined in the annotation
	 *
	 * @param string $paramName
	 * @param string $sourceClass
	 * @param string $sourceMethod
	 *
	 * @return string
	 */
	private static function getParamType($paramName, \ReflectionMethod $methodRefl)
	{
		$phpDoc = new \Foomo\Reflection\PhpDocEntry($methodRefl->getDocComment());
		$propertyDef = '';
		foreach ($methodRefl->getParameters() as $paramRefl) {
			/* @var $paramRefl \ReflectionParameter */
			if ($paramRefl->name == $paramName) {
				$propertyDef = new CacheResourcePropertyDefinition($paramRefl, $phpDoc);
				break;
			}
		}
		return $propertyDef->type;
	}

}