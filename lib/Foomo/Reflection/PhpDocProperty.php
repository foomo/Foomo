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

/**
 * for properties described by @property
 */
class PhpDocProperty {

	/**
	 * name of the property
	 *
	 * @var string
	 */
	public $name;
	/**
	 * type of the property
	 *
	 * @var string
	 */
	public $type;
	/**
	 * comment
	 *
	 * @var string
	 */
	public $comment;
	/**
	 * true if readable
	 *
	 * @var boolean
	 */
	public $read = true;
	/**
	 * true if writable
	 *
	 * @var boolean
	 */
	public $write = true;
	public function __construct($name, $type, $comment, $read = true, $write = true)
	{
		$this->name = $name;
		$this->type = $type;
		$this->comment = $comment;
		$this->read = $read;
		$this->write = $write;
	}

}