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

namespace Foomo\Modules\Resource;

/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author franklin <franklin@weareinteractive.com>
 */
class ClassName extends \Foomo\Modules\Resource
{
	//---------------------------------------------------------------------------------------------
	// ~ Variables
	//---------------------------------------------------------------------------------------------

	/**
	 * Class name that should exist
	 * 
	 * @var string
	 */
	private $className;

	//---------------------------------------------------------------------------------------------
	// ~ Constructor
	//---------------------------------------------------------------------------------------------

	/**
	 * @param string $className Class name that should exist
	 */
	private function __construct($className)
	{
		$this->className = $className;
	}

	//---------------------------------------------------------------------------------------------
	// ~ Public methods
	//---------------------------------------------------------------------------------------------

	/**
	 * @return string
	 */
	public function getClassName()
	{
		return $this->className;
	}

	/**
	 * @return boolean
	 */
	public function resourceValid()
	{
		return (\class_exists($this->className));
	}

	/**
	 * @return string
	 */
	public function resourceStatus()
	{
		return 'Class "' . $this->className . '"' . ((!$this->resourceValid()) ? ' is missing' : ' is ok');
	}

	/**
	 * @return string
	 */
	public function tryCreate()
	{
		if ($this->resourceValid()) {
			return ' class "' . $this->link . '" exists';
		} else {
			return ' class "' . $this->link . '" is missing';
		}
	}

	//---------------------------------------------------------------------------------------------
	// ~ Public static methods
	//---------------------------------------------------------------------------------------------

	/**
	 * @param string $className Class name that should exist
	 * @return Foomo\Modules\Resource\ClassName
	 */
	public static function getResource($className)
	{
		return new self($className);
	}
}