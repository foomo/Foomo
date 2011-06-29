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

namespace Foomo\MVC\Controller;

class ActionParameter {

	/**
	 * @var string
	 */
	public $type = 'string';
	/**
	 * @var boolean
	 */
	public $optional = false;
	/**
	 * @var string
	 */
	public $name;
	private $value;
	public function __construct($value = null, $name = null, $type = 'string', $optional = false)
	{
		$this->name = $name;
		$this->value = $value;
		$this->type = $type;
		$this->optional = $optional;
	}

	public function setValue($newValue)
	{
		$this->value = $newValue;
	}

	public function getValue()
	{
		return $this->value;
	}

}