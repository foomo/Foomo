<?php

/*
 * bestbytes-copyright-placeholder
 */

namespace Foomo\MVC\Controller;

/**
 * class describing a reflectoed method on a controller or class in general
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
			/* @var $parm Foomo\MVC\Controller\ActionParameter */
			if ($parm->optional) {
				$this->optionalParameterCount++;
			}
		}
	}

}
