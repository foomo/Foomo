<?php

namespace Foomo\Frontend\ToolboxConfig;

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