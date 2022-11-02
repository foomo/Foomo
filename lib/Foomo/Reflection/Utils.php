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

namespace Foomo\Reflection;
use ReflectionParameter,
	ReflectionClass;
/**
 * @link	www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author	franklin <franklin@weareinteractive.com>
 */
class Utils
{
	//---------------------------------------------------------------------------------------------
	// ~ Constructor
	//---------------------------------------------------------------------------------------------

	/**
	 * @param string $className
	 * @param array $args
	 * @return stdClass
	 */
	public static function createInstance($className, $args=array())
	{
		if (count($args) == 0) {
			return new $className;
		} else {
			$reflection = new \ReflectionClass($className);
			return $reflection->newInstanceArgs($args);
		}
	}

	public static function isPHP8(): bool {
		return strpos(phpversion(), '8') === 0;
	}

	/**
	 * Workaround for replacing the PHP 8 deprecated \ReflectionParameter::getClass() function
	 * @param $param \ReflectionParameter
	 * @return null|\ReflectionClass
	 */
	public static function getClass($param) {
		return $param->getType() && !$param->getType()->isBuiltin() ? new ReflectionClass($param->getType()->getName()) : null;
	}

	/**
	 * Workaround for replacing the PHP 8 deprecated \ReflectionParameter::isArray() function
	 * @param $param \ReflectionParameter
	 * @return bool
	 */
	public static function isArray($param) {
		return $param->getType() && $param->getType()->getName() === 'array';
	}
}