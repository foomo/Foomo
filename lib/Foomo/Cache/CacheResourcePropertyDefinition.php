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
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
class CacheResourcePropertyDefinition {

	public $name;
	public $type = 'string';
	public $defaultValue = null;
	public $isOptional = false;

	public function __construct(\ReflectionParameter $paramRefl, \Foomo\Reflection\PhpDocEntry $phpDoc)
	{
		$this->name = $paramRefl->getName();
		$this->isOptional = $paramRefl->isOptional();
		if ($this->isOptional) {
			$this->defaultValue = $paramRefl->getDefaultValue();
		}
		/* @var $docParam Foomo\Reflection\PhpDocArg */
		foreach ($phpDoc->parameters as $docParam) {
			if ($docParam->name == $paramRefl->getName()) {
				$this->type = $docParam->type;
				break;
			}
		}
		if (!$this->type) {
			$this->type = 'string';
		}
	}

	/**
	 * check if the type is an array (of)
	 *
	 * @return boolean
	 */
	public function typeIsArray()
	{
		if ($this->type == 'array') {
			return true;
		} else {
			if (strpos($this->type, '[]') == \strlen($this->type) - 2) {
				return true;
			} else {
				return false;
			}
		}
	}

}