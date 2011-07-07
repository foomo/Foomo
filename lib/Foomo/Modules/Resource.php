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

namespace Foomo\Modules;

/**
 * if you module needs a resource extend this class be our guest
 * 
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
abstract class Resource {
	public $isNiceToHave = false;
	/**
	 * @todo discuss naming
	 * @internal
	 * @var boolean
	 */
	public $isRequired = false;
	/**
	 * chaining config
	 * @todo discuss this
	 *
	 * @return Foomo\Modules\Resource
	 */
	public function isRequired($required = true)
	{
		$this->isRequired = $required;
		return $this;
	}
	/**
	 * mark as nice to have
	 *
	 * @param type $isNiceToHave
	 *
	 * @return Foomo\Modules\Resource
	 */
	public function isNiceToHave($isNiceToHave = true)
	{
		$this->isNiceToHave = $isNiceToHave;
		return $this;
	}
	/**
	 * check if the resource is valid
	 *
	 * @return boolean
	 */
	abstract public function resourceValid();

	/**
	 * tell sth. human readable about the status of the resource
	 *
	 * @return string
	 */
	abstract public function resourceStatus();

	/**
	 * try to create the resource
	 *
	 * @return string a report of what happened
	 */
	abstract public function tryCreate();
}