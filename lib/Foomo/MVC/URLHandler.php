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

use Foomo\MVC;
use Foomo\Timer;

/**
 * handle urls for MVC
 *
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 * @internal
 */
class URLHandler {
	/**
	 * @internal
	 * @var array
	 */
	public static $rawCurrentCallData;

	/**
	 * @var array
	 */
	private $cache;
	/**
	 * url do build and parse up on
	 *
	 * @var string
	 */
	public $baseURL;
	/**
	 * last action that was called
	 * @var string
	 */
	public $lastAction;
	/**
	 * last action was called with these parameters
	 *
	 * @var array
	 */
	public $lastParameters;
	/**
	 *
	 * @var path of this controller helper
	 */
	public $path;
	/**
	 * id of the application
	 *
	 * @var string
	 */
	private $appId;
	/**
	 * controller class name
	 *
	 * @var string
	 */
	private $controllerClassName;
	private $appClassName;
	private static $instanceCounter = array();
	/**
	 * class / controller interface information
	 *
	 * @var array
	 */
	private static $classCache = array();
	public function __construct(AbstractApp $app, $baseURL = null)
	{
		$this->appClassName = get_class($app);
		$this->appId = MVC::getAppName($this->appClassName);
		if (is_null($baseURL)) {
			$baseURL = $_SERVER['PHP_SELF'];
		}
		$this->baseURL = $baseURL;
		$this->controllerClassName = get_class($app->controller);
		// count instances
		if (!isset(self::$instanceCounter[$this->controllerClassName])) {
			self::$instanceCounter[$this->controllerClassName] = 0;
		} else {
			self::$instanceCounter[$this->controllerClassName]++;
		}
		//var_dump(self::$instanceCounter);
	}

	public static function resetInstanceCounter()
	{
		self::$instanceCounter = array();
	}

	private function loadClass($class)
	{
		if (is_string($class)) {
			$className = $class;
		} else {
			$className = get_class($class);
		}
		if (!isset(self::$classCache[$className])) {
			if (!class_exists($className)) {
				throw new \InvalidArgumentException('invalid class >' . $className . '<');
			}
			// we might wanna do some caching here ...
			self::$classCache[$className] = Controller\ActionReader::read($className);
		}
		return self::$classCache[$className];
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
	 * get the controller id
	 *
	 * @param string $className
	 *
	 * @return string get the instance name of my controller instance
	 */
	public function getControllerId()
	{
		$constantName = $this->appClassName . '::CONTROLLER_ID';

		if (!defined($constantName)) {
			$id = $this->appId;
		} else {
			$id = constant($constantName);
		}

		if (isset(self::$instanceCounter[$this->controllerClassName]) && self::$instanceCounter[$this->controllerClassName] > 0) {
			$id .= '-' . self::$instanceCounter[$this->controllerClassName];
		}

		return $id;
	}
	/**
	 * get method and paramaeters to call a controller on an app
	 * 
	 * @param \Foomo\MVC\AbstractApp $app
	 * @param string $baseURL
	 * @param string $uri
	 * 
	 * @return array
	 */
	public static function getAppCallData(AbstractApp $app, $baseURL = null, $uri = null)
	{
		$handler = new self($app, $baseURL);
		return $handler->getCallData($app, $uri);
	}

	public function getCallData(AbstractApp $app, $uri = null)
	{
        // Timer::start(__METHOD__);
		if(is_null($uri)) {
			$uri = $_SERVER['REQUEST_URI'];
		}
		if (!isset($this->baseURL)) {
			$this->baseURL = MVC::getBaseUrl();
		}
		$controller = $app->controller;
		$alternativeSource = $_REQUEST;
		// load information about the

		$controllerCache = $this->loadClass($controller);

		$URI = substr($uri, strlen($this->baseURL) + 1);
		$parts = explode('/', $URI);
		$cleanParts = array();
		foreach ($parts as $part) {
			array_push($cleanParts, urldecode($part));
		}
		self::$rawCurrentCallData = $cleanParts;
		$className = get_class($controller);
		$this->path = $this->baseURL;
		$id = $this->getControllerId(get_class($controller));

		$action = 'actionDefault';
		$parms = array();

		if (isset($cleanParts[0]) && ($cleanParts[0] == $className || $cleanParts[0] === $id)) {
			$this->path .= '/' . $id;
			if (count($cleanParts) > 1) {
				foreach ($controllerCache as $actionId => $controllerAction) {
					/* @var $controllerAction Controller\Action */
					if ($this->isThisAction($cleanParts[1], $controllerAction)) {//strtolower($action->actionName) == strtolower($cleanParts[1]) || strtolower($actionId) == strtolower($cleanParts[1])) {
						// readArgs
						// try to pad
						$this->path .= '/' . $cleanParts[1];
						if (count($cleanParts) - 2 < count($controllerAction->parameters)) {
							$parameterDiff = count($controllerAction->parameters) - (count($cleanParts) - 2);
							$keys = array_keys($controllerAction->parameters);
							for ($iParm = count($controllerAction->parameters) - $parameterDiff; $iParm < count($controllerAction->parameters); $iParm++) {
								if (isset($alternativeSource[$keys[$iParm]])) {
									array_push($cleanParts, $alternativeSource[$keys[$iParm]]);
								} else {
									array_push($cleanParts, null);
								}
							}
						}
						$parms = array();
						$inputParmCount = count($cleanParts) - 2;
						if ($inputParmCount >= $controllerAction->optionalParameterCount) {
							$i = 1;
							foreach ($controllerAction->parameters as $parameter) {
								/* @var $parameter Controller\ActionParameter */
								$i++;
								if (!isset($cleanParts[$i])) {
									$parms[] = null;
									// break;
								} else {
									$parms[] = self::castParameterToSanitized($parameter, $cleanParts[$i]);
									switch (true) {
										case is_scalar($cleanParts[$i]):
											$this->path .= '/' . urlencode($cleanParts[$i]);
											break;
										case is_array($cleanParts[$i]):
										case is_object($cleanParts[$i]):
											$this->path .= '/' . urlencode(serialize($cleanParts[$i]));
											trigger_error('i will not be able to understand what i am doing here ;) ' . $this->path, E_USER_WARNING);
											break;
										default:
											trigger_error('how should I press that to a url');
									}
								}
							}
						}

						$action = $controllerAction->actionName;

						break;
					}
				}
			}
		}

		$ret = array(
			'instance' => self::$instanceCounter[$this->controllerClassName],
			'instanceName' => $this->getControllerId(),
			'action' => $action,
			'parms' => $parms
		);
        // Timer::stop(__METHOD__);
		return $ret;
	}

	public function control(AbstractApp $app)
	{
		$callData = $this->getCallData($app);

		if ($callData) {
			$method = array($app->controller, $callData['action']);
			$this->lastAction = $callData['action'];
			$this->lastParameters = $callData['parms'];
			if (!is_callable($method)) {
				if ($callData['action'] != 'actionDefault') {
					trigger_error('can not call ' . $callData['action'], E_USER_WARNING);
				}
				return null;
			} else {
				$ret = call_user_func_array($method, $callData['parms']);
			}
		} else {
			$ret = null;
			$this->lastAction = '';
			$this->lastParameters = array();
		}
		return $ret;
	}

	/**
	 * Sanitize input
	 *
	 * @param Controller\ActionParameter $parameter
	 * @param mixed $value
	 * @internal
	 *
	 * @return mixed
	 */
	public static function castParameterToSanitized(Controller\ActionParameter $parameter, $value)
	{
		if(class_exists($parameter->type)) {
			$refl = new \ReflectionClass($parameter->type);
			if($refl->implementsInterface(__NAMESPACE__ . '\\SanitizerInterface')) {
				$value = new $parameter->type($value);
			}
		}
		return $value;
	}

	/**
	 * Renders a link that will trigger a method on a given controller classname with the given parameters
	 *
	 * @param string $className
	 * @param string $methodName
	 * @param array $parameters array of parameters in the right order
	 *
	 * @return string URL to use as a link in a view
	 */
	public function renderURL($className, $methodName = 'actionDefault', $parameters = array())
	{
		if (strpos($methodName, '/') === 0) {
			// experimental deeplink
			$ret = $this->baseURL . $methodName;
		} else {
			$classCache = $this->loadClass($className);
			// strip "action" from the method name
			if (strpos($methodName, 'action') === 0) {
				$methodName = strtolower(substr($methodName, 6, 1)) . substr($methodName, 7);
			}
			// check if there is a short name on the controller
			if (class_exists($className)) {
				$className = $this->getControllerId($className);
			}
			// concatenate with the instance information
			/*
			  if(self::$instanceCounter[$this->controllerClassName] > 0) {
			  $instanceSuffix = '-' . self::$instanceCounter[$this->controllerClassName];
			  } else {
			  $instanceSuffix = '';
			  }
			  $instanceSuffix = '';
			  $classIdentifier = $className . $instanceSuffix;
			 */
			// add the (stripped) method name
			$ret = $this->baseURL . '/' . urlencode($classIdentifier = $this->getControllerId()) . '/' . urlencode($methodName);
			// append paramters
		}
		foreach ($parameters as $parameter) {
			$ret .= '/' . urlencode($parameter);
		}
		return $ret;
	}

	/**
	 * render a merhod url
	 *
	 * @internal
	 * @param string $methodName
	 * @param array $parameters
	 * @return string
	 */
	public function renderMethodUrl($methodName = 'actionDefault', $parameters = array())
	{
		return $this->renderUrl($this->controllerClassName, $methodName, $parameters);
	}

	/**
	 * some debugging information
	 *
	 * @return string
	 */
	public function renderState()
	{
		$ret = __CLASS__ . ' status ' . PHP_EOL;
		$ret .= '  loaded classes' . PHP_EOL;
		foreach ($this->cache as $className => $actions) {
			/* @var $action RController\Action */
			$ret .= '    ' . $className . PHP_EOL;
			foreach ($actions as $actionId => $action) {
				$ret .= '      ' . $actionId . ' => ' . $action->actionName . '(';
				$parms = array();
				foreach ($action->parameters as $parameter) {
					/* @var $parameter Controller\ActionParameter */
					array_push($parms, $parameter->type . ' $' . $parameter->name);
				}
				$ret .= implode(', ', $parms) . ')' . PHP_EOL;
			}
		}
		return $ret;
	}

	/**
	 * cast to string => show debugging information - maybe we should only allow that in non production
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->renderState();
	}
}