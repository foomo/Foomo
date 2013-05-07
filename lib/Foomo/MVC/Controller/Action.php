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

/**
 * class describing a reflectoed method on a controller or class in general
 *
 * @internal
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
class Action {

	/**
	 * @var string
	 */
	public $controllerName;
	/**
	 * @var string
	 */
	public $actionName;
	/**
	 * @var string
	 */
	public $actionNameShort;
	/**
	 * @var ActionParameter[]
	 */
	public $parameters = array();
	/**
	 * @param string $controllerName
	 * @param atring $actionName
	 * @param  ActionParameter[] $parameters
	 */
	public $optionalParameterCount;
	public function __construct($controllerName, $actionName, $parameters)
	{
		$this->controllerName = $controllerName;
		$this->actionName = $actionName;
		$this->parameters = $parameters;
		if (strpos($actionName, 'action') === 0) {
			$cutName = substr($actionName, strlen('action'));
		} else {
			$cutName = $actionName;
		}
		$this->actionNameShort = strtolower(substr($cutName, 0, 1)) . substr($cutName, 1);
		$this->optionalParameterCount = 0;
		foreach ($this->parameters as $parm) {
			/* @var $parm ActionParameter */
			if ($parm->optional) {
				$this->optionalParameterCount++;
			}
		}
	}
	public function isMagic()
	{
		return substr($this->actionName, 6, 2) == '__';
	}
}
