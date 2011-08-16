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

namespace Foomo\Session;

/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
class ImmutableProxy
{
	//---------------------------------------------------------------------------------------------
	// ~ Variables
	//---------------------------------------------------------------------------------------------

	private $obj;

	//---------------------------------------------------------------------------------------------
	// ~ Constructor
	//---------------------------------------------------------------------------------------------

	public function __construct($obj)
	{
		if(!is_object($obj)) throw new \InvalidArgumentException('object expected for $obj');
		$this->obj = $obj;
	}

	//---------------------------------------------------------------------------------------------
	// ~ Magic methods
	//---------------------------------------------------------------------------------------------

	public function __set($name, $value)
	{
		throw new \Exception('you have to lock the session, before you write to it');
	}

	public function __get($name)
	{
		if (!isset($this->obj->$name)) trigger_error('property ' . $name . ' does not exist on ' . get_class($this->obj), E_USER_NOTICE);
		return $this->obj->$name;
	}

	public function __call($name, $args)
	{
		return call_user_func_array(array($this->obj, $name), $args);
	}
}