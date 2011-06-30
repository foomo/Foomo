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

namespace Foomo\Frontend\ToolboxConfig;

/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author franklin <franklin@weareinteractive.com>
 */
class MenuEntry
{
	//---------------------------------------------------------------------------------------------
	// ~ Variables
	//---------------------------------------------------------------------------------------------

	/**
	 * @var array
	 */
	public $path;
	/**
	 * @var string
	 */
	public $name;
	/**
	 * @var string
	 */
	public $module;
	/**
	 * @var string
	 */
	public $app;
	/**
	 * @var string
	 */
	public $action;
	/**
	 * @var string
	 */
	public $target;

	//---------------------------------------------------------------------------------------------
	// ~ Constructor
	//---------------------------------------------------------------------------------------------

	/**
	 * @param string $path
	 * @param string $name
	 * @param string $module
	 * @param string $app
	 * @param string $action
	 * @param string $target
	 * @return Foomo\Frontend\ToolboxConfig\MenuEntry
	 */
	public function __construct($path, $name, $module, $app, $action='default', $target='_self')
	{
		$this->path = explode('.', $path);
		$this->name = $name;
		$this->module = $module;
		$this->app = $app;
		$this->action = $action;
		$this->target = $target;
	}

	//---------------------------------------------------------------------------------------------
	// ~ Public methods
	//---------------------------------------------------------------------------------------------

	/**
	 * @return array
	 */
	public function toArray()
	{
		return array(
			'name' => $this->name,
			'module' => $this->module,
			'app' => $this->app,
			'action' => $this->action,
			'target' => $this->target,
		);
	}

	//---------------------------------------------------------------------------------------------
	// ~ Public static methods
	//---------------------------------------------------------------------------------------------

	/**
	 * @param string $path
	 * @param string $name
	 * @param string $module
	 * @param string $app
	 * @param string $action
	 * @param string $target
	 * @return Foomo\Frontend\ToolboxConfig\MenuEntry
	 */
	public static function create($path, $name, $module, $app, $action='default', $target='_self')
	{
		return new self($path, $name, $module, $app, $action, $target);
	}
}