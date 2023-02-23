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

namespace Foomo\MVC;

use Foomo\MVC\Controller\ActionReader;
use Foomo\MVC\Controller\Action;
use Foomo\MVC\Controller\ActionParameter;
use Foomo\HTMLDocument;

/**
 * helps to control controllers ;) do not touch this ons
 * 
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 * @deprecated but still need it for Hiccup and Setup
 * @todo add routing support
 * @internal *very* much so
 */
class ControllerHelper {
	const MODE_GET_PARMS = 'modeGet';
	const MODE_PATH = 'modeUrl';
	const ERROR_CLASS_NOT_FOUND = 'class could not be found';
	const ERROR_CODE_CLASS_NOT_FOUND = 1;
	/**
	 * @var array
	 */
	private $cache;
	private $baseURI;
	private $mode = self::MODE_GET_PARMS;
	/**
	 * last action that was called
	 * @var string
	 */
	public $lastAction;
	/**
	 *
	 * @var string path of this controller helper
	 */
	public $path;
	private static $appInstanceCounter = array();
	public function __construct($mode = null)
	{
		$this->baseURI = $_SERVER['PHP_SELF'];
		if ($mode) {
			$this->mode = $mode;
		}
		$this->cache = array();
	}

	/**
	 * choose between a beautiful URL - looking like a path or go for GET parameter style
	 *
	 * @param string $mode ControllerHelper::MODE_PATH || ControllerHelper::MODE_GET
	 */
	public function setMode($mode)
	{
		$this->mode = $mode;
	}

	private function loadClass($class)
	{
		if (is_string($class)) {
			$className = $class;
		} else {
			$className = get_class($class);
		}
		if (!isset($this->cache[$className])) {
			if (!class_exists($className)) {
				trigger_error(self::ERROR_CLASS_NOT_FOUND . ' ' . $className, E_USER_NOTICE);
				throw new Exception(self::ERROR_CLASS_NOT_FOUND . ' >' . $className . '<'); //, self::ERROR_CLASS_NOT_FOUND);
			}
			$this->cache[$className] = ActionReader::read($className);
		}
		return $this->cache[$className];
	}

	/**
	 * this helps the controllerhelper to understand where from to parse
	 *
	 * @param string $baseURI
	 */
	public function setBaseURI($baseURI)
	{
		$this->baseURI = $baseURI;
	}

	public function getBaseURI()
	{
		return $this->baseURI;
	}

	private function controlAppGet($app, $input)
	{
		$appCache = $this->loadClass($app);
		if (isset($input['action'])) {
			foreach ($appCache as $actionId => $action) {
				/* @var $action Foomo\MVC\Controller\Action */
				$execute = false;
				if ($this->isThisAction($input['action'], $action)) {
					$prefix = '';
					$execute = true;
					$callParms = array();
					foreach ($action->parameters as $parameter) {
						/* @var $parameter Foomo\MVC\Controller\ActionParameter */
						if (isset($input[$parameter->name])) {
							$callParms[] = $input[$parameter->name];
						} else {
							if (!$parameter->optional) {
								$execute = false;
								break;
							}
						}
						$prefix = ', ';
					}
					if ($execute) {
						break;
					}
				}
			}
		} else {
			$execute = false;
		}
		if ($execute) {
			$this->lastAction = $action->actionName;
			return call_user_func_array(array($app, $action->actionName), $callParms);
		} elseif (isset($appCache['default'])) {
			$this->lastAction = 'actionDefault';
			return $app->actionDefault();
		} else {
			$this->lastAction = '';
			return '';
		}
	}

	private function isThisAction($input, $action)
	{
		if ($action->actionName == $input || $action->actionNameShort == $input) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * have your app being called by the controller - XSS is still your responsibility - but there are some handy helpers
	 *
	 * @param mixed $app instance of some class
	 * @param string $alternativeSource if you do not want to use $_REQUEST for incoming paramters, the pass them here
	 *
	 * @return mixed
	 */
	public function control($app, $alternativeSource = null)
	{
		if ($alternativeSource === null) {
			$alternativeSource = $_REQUEST;
		}
		if ($this->mode == self::MODE_GET_PARMS) {
			return $this->controlAppGet($app, $alternativeSource);
		} else {
			return $this->controlAppPath($app, $alternativeSource);
		}
	}

	/**
	 * experimental static interface
	 *
	 * @param mixed $app name of the class for static calls or instance
	 * @param array $alternativeSource source for parameters
	 * @param string $baseUri endpoint
	 * @param string $mode
	 * @return mixed
	 */
	public static function run($app, $alternativeSource = null, $baseUri = null, $mode = null)
	{
		if (is_null($baseUri)) {
			$baseUri = $_SERVER['PHP_SELF'];
		}
		if (is_null($mode)) {
			$mode = self::MODE_GET_PARMS;
		}
		$helper = new self($mode);
		$helper->setBaseURI($baseUri);
		return $helper->control($app, $alternativeSource);
	}

	public static function runAsHtml($app, $alternativeSource = null, $baseUri = null, $mode = null, $title = null)
	{
		if (!isset($title)) {
			$title = constant(get_class($app) . '::CONTROLLER_ID');
		}
		$doc = HTMLDocument::getInstance();
		$doc->addBody(self::run($app, $alternativeSource, $baseUri, $mode));
		return $doc->output();
	}

	/**
	 * if you wnt to link somewhere else, here you are
	 *
	 * @param string $className
	 * @param string $methodName
	 * @param array $parameters
	 * @param string $baseUri
	 * @param string $mode
	 *
	 * @return string
	 */
	public static function staticRenderAppLink($className, $methodName, $parameters = array(), $baseUri = null, $mode = 'modeGet')
	{
		static $helper;
		if (!isset($helper)) {
			$helper = new self();
		}
		if (is_null($baseUri)) {
			$baseUri = $_SERVER['PHP_SELF'];
		}
		$helper->setMode($mode);
		$helper->setBaseURI($baseUri);
		return $helper->renderAppLink($className, $methodName, $parameters);
	}

	private function controlAppPath($app, $alternativeSource)
	{

		$appCache = $this->loadClass($app);
		$URI = substr($_SERVER['REQUEST_URI'], strlen($this->baseURI) + 1);
		$parts = explode('/', $URI);
		$cleanParts = array();
		foreach ($parts as $part) {
			array_push($cleanParts, urldecode($part));
		}
		$className = get_class($app);

		// count instances
		if (!isset(self::$appInstanceCounter[$className])) {
			self::$appInstanceCounter[$className] = 0;
		} else {
			self::$appInstanceCounter[$className]++;
		}


		$this->path = $this->baseURI;
		$id = constant($className . '::CONTROLLER_ID');
		if (isset(self::$appInstanceCounter[$className]) && self::$appInstanceCounter[$className] > 0) {
			$id .= '-' . self::$appInstanceCounter[$className];
		}
		if (isset($cleanParts[0]) && ($cleanParts[0] == $className || $cleanParts[0] === $id)) {
			$this->path .= '/' . $id;
			foreach ($appCache as $actionId => $action) {
				/* @var $action Foomo\MVC\Controller\Action */
				if ($this->isThisAction($cleanParts[1], $action)) {//strtolower($action->actionName) == strtolower($cleanParts[1]) || strtolower($actionId) == strtolower($cleanParts[1])) {
					// readArgs
					// try to pad
					$this->path .= '/' . $cleanParts[1];
					if (count($cleanParts) - 2 < count($action->parameters)) {
						$parameterDiff = count($action->parameters) - (count($cleanParts) - 2);
						$keys = array_keys($action->parameters);
						for ($iParm = count($action->parameters) - $parameterDiff; $iParm < count($action->parameters); $iParm++) {
							if (isset($alternativeSource[$keys[$iParm]])) {
								array_push($cleanParts, $alternativeSource[$keys[$iParm]]);
							}
						}
					}
					$parms = array();
					if (count($cleanParts) - 2 >= count($action->parameters)) {
						$i = 1;
						foreach ($action->parameters as $parameter) {
							/* @var $parameter Foomo\MVC\Controller\ActionParameter */
							$i++;
							$parms[] = URLHandler::castParameterToSanitized($parameter, $cleanParts[$i]);
							$this->path .= '/' . urlencode($cleanParts[$i]);
						}
					}
					$ret = call_user_func_array(array($app, $action->actionName), $parms);
					$this->lastAction = $action->actionName;
					return $ret;
				}
			}
		}
		if (isset($appCache['default'])) {
			$this->lastAction = 'actionDefault';
			return $app->actionDefault();
		}
		$this->lastAction = '';
		return '';
	}
	/**
	 * Renders a link that will trigger a method on a given controller classname with the given parameters
	 *
	 * @param string $className
	 * @param string $methodName
	 * @param array $parameters array of parameters in the right order
	 * @return string URL to use as a link in a view
	 */
	public function renderAppLink($className, $methodName = 'actionDefault', $parameters = array())
	{
		$classCache = $this->loadClass($className);
		if (isset(self::$appInstanceCounter[$className]) && self::$appInstanceCounter[$className] > 0) {
			$instanceSuffix = '-' . self::$appInstanceCounter[$className];
		} else {
			$instanceSuffix = '';
		}
		if (strpos($methodName, 'action') === 0) {
			$methodName = strtolower(substr($methodName, 6, 1)) . substr($methodName, 7);
		}
		if (null != ($id = constant($className . '::CONTROLLER_ID'))) {
			$className = $id;
		}
		/* @var $parameter Foomo\MVC\Controller\ActionParameter */
		switch ($this->mode) {
			case self::MODE_GET_PARMS:
				$ret = $this->baseURI;
				if (strpos($this->baseURI, '?') === false) {
					$ret .= '?class=' . urlencode($className);
				} else {
					$ret .= '&amp;class=' . urlencode($className);
				}
				$ret .= '&amp;action=' . $methodName;
				foreach ($parameters as $parameterName => $parameter) {
					$ret .= '&amp;';
					if (!is_object($parameter)) {
						$ret .= $parameterName . '=' . $parameter;
					} else {
						$ret .= urlencode($parameter->name) . '=' . $parameter->getValue();
					}
				}
				break;
			case self::MODE_PATH:
				$classIdentifier = $className . $instanceSuffix;
				$ret = $this->baseURI . '/' . urlencode($classIdentifier) . '/' . urlencode($methodName);
				foreach ($parameters as $parameter) {
					$ret .= '/' . urlencode($parameter);
				}
				break;
		}
		return $ret;
	}

	public function renderState()
	{
		$ret = __CLASS__ . ' status ' . PHP_EOL;
		$ret .= '  loaded classes' . PHP_EOL;
		foreach ($this->cache as $className => $actions) {
			/* @var $action Foomo\MVC\Controller\Action */
			$ret .= '    ' . $className . PHP_EOL;
			foreach ($actions as $actionId => $action) {
				$ret .= '      ' . $actionId . ' => ' . $action->actionName . '(';
				$parms = array();
				foreach ($action->parameters as $parameter) {
					/* @var $parameter Foomo\MVC\Controller\ActionParameter */
					array_push($parms, $parameter->type . ' $' . $parameter->name);
				}
				$ret .= implode(', ', $parms) . ')' . PHP_EOL;
			}
		}
		return $ret;
	}

	public function __toString()
	{
		return $this->renderState();
	}

}