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

use Foomo\Cache\CacheResourcePropertyDefinition;

/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
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