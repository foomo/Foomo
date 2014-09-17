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
	 * expose the class id in the url
	 *
	 * @var bool
	 */
	protected static $exposeClassId = true;
	/**
	 * if set to true missing parameters will not be padded - you will get a 404
	 * @var bool
	 */
	protected static $strictParameterHandling = false;
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
	}

	public static function resetInstanceCounter()
	{
		self::$instanceCounter = array();
	}
	/**
	 * be strict about parameters or not
	 *
	 * @param bool $strict
	 */
	public static function strictParameterHandling($strict)
	{
		self::$strictParameterHandling = (bool) $strict;
	}
	/**
	 * show the class id in urls
	 *
	 * @param bool $expose
	 */
	public static function exposeClassId($expose)
	{
		self::$exposeClassId = (bool) $expose;
	}

	/**
	 * actions for the controller
	 *
	 * @param string $class
	 *
	 * @return Controller\Action[]
	 *
	 * @throws \InvalidArgumentException
	 */
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
		if ($action->actionName == $input || $action->actionNameShort == $input || ($input === '' && $action->actionName === 'actionDefault')) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * get the controller id
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
	protected function getCallPath($uri = null)
	{
		if(is_null($uri)) {
			$uri = $_SERVER['REQUEST_URI'];
		}
		$queryPos = strpos($uri, '?');
		if($queryPos !== false) {
			$uri = substr($uri, 0, $queryPos);
		}
		if(!isset($this->baseURL)) {
			$this->baseURL = MVC::getBaseUrl();
		}
		return substr($uri, strlen($this->baseURL) + 1);
	}
	public function getCallData(AbstractApp $app, $uri = null, array $alternativeSource = null)
	{
		if(is_null($alternativeSource)) {
			$alternativeSource = $_REQUEST;
		}

		$cleanParts = self::urlDecodePath($this->getCallPath($uri));

		$className = get_class($app->controller);
		$this->path = $this->baseURL;

		$action = '';
		$parameters = array();
		$classId = $this->getControllerId($className);

		if(self::$exposeClassId) {
			$classMatch = isset($cleanParts[0]) && ($cleanParts[0] == $className || $cleanParts[0] === $classId);
		} else {
			$classMatch = true;
			$cleanParts = array_merge(array($classId), $cleanParts);
		}
		self::$rawCurrentCallData = $cleanParts;
		$class = get_class($app->controller);
		if(self::$exposeClassId && $classMatch || !self::$exposeClassId) {
			if(self::$exposeClassId) {
				$this->path .= '/' . urlencode($classId);
			}
			if(count($cleanParts) > 1) {
				foreach ($this->loadClass($app->controller) as $controllerAction) {
					/* @var $controllerAction Controller\Action */
					if(substr($cleanParts[1], 0, 2) == '__' || substr($cleanParts[1], 0, 8) == 'action__') {
						// we are skipping magic stuff like 404s
						continue;
					}
					if ($this->isThisAction($cleanParts[1], $controllerAction)) {
						// readArgs
						$rawParameters = array_slice($cleanParts, 2);
						self::padParameters($controllerAction, $rawParameters, $alternativeSource);
						// does the parameter count match?
						$parameterCount = count($controllerAction->parameters);
						if(self::$strictParameterHandling && (count($rawParameters) < $parameterCount - $controllerAction->optionalParameterCount)) {
							// if the action was right, but the parameters were not,
							// => a 404

							break;
						}
						// sanitize input
						$parameters = self::sanitizeInput($controllerAction, $rawParameters);

						$this->path .= '/' . $controllerAction->actionNameShort . self::renderPathParameters($rawParameters, $parameterCount);
						$action = $controllerAction->actionName;
						$class = $controllerAction->controllerName;

						break;
					}
				}
			}
		} else {
			$actions = $this->loadClass($app->controller);
			$action = $actions['default']->actionName;
			$class = $actions['default']->controllerName;
		}
		if(empty($action)) {
			$action = $this->get404Action($app);
			// @todo what is with the path
		}
		$ret = array(
			'instance' => self::$instanceCounter[$this->controllerClassName],
			'instanceName' => $this->getControllerId(),
			'class' => $class,
			'action' => $action,
			'parms' => $parameters
		);
		return $ret;
	}
	private static function urlDecodePath($path)
	{
		$cleanParts = array();
		$parts = explode('/', $path);
		foreach ($parts as $part) {
			if(strpos($part, ',') !== false) {
				// an array
				$array = explode(',', $part);
				array_walk($array, function(&$item) {
					$item = urldecode($item);
				});
				$cleanParts[] = $array;
			} else {
				// just a string
				array_push($cleanParts, urldecode($part));
			}
		}
		return $cleanParts;
	}
	protected static function sanitizeInput(MVC\Controller\Action $controllerAction, &$cleanParts)
	{
		$parameters = array();
		$i = 0;
		foreach ($controllerAction->parameters as $parameter) {
			/* @var $parameter Controller\ActionParameter */
			if(isset($cleanParts[$i])) {
				$parameters[] = self::castParameterToSanitized($parameter, $cleanParts[$i]);
			} else if($parameter->optional) {
				$parameters[] = $parameter->defaultValue;
			}
			$i ++;
		}
		return $parameters;

	}

	/**
	 * try to pad parameters from the alternative source
	 *
	 * @param Controller\Action $controllerAction
	 * @param array $parameters
	 * @param array $alternativeSource
	 */
	protected static function padParameters(MVC\Controller\Action $controllerAction, array &$parameters, array &$alternativeSource)
	{
		$parameterDiff = count($controllerAction->parameters) - count($parameters);
		$keys = array_keys($controllerAction->parameters);
		for ($iParm = count($controllerAction->parameters) - $parameterDiff; $iParm < count($controllerAction->parameters); $iParm++) {
			if (isset($alternativeSource[$keys[$iParm]])) {
				array_push($parameters, $alternativeSource[$keys[$iParm]]);
			} else if(!self::$strictParameterHandling) {
				array_push($parameters, null);
			}
		}
	}

	/**
	 * @param array $cleanParts
	 * @param integer $parameterCount
	 *
	 * @return string
	 */
	protected static function renderPathParameters(array $cleanParts, $parameterCount)
	{
		$parameters = '';
		$i = 0;
		foreach ($cleanParts as $parameter) {
			if($i == $parameterCount) {
				break;
			}
			/* @var $parameter Controller\ActionParameter */
			switch (true) {
				case is_scalar($parameter) || is_null($parameter):
					$parameters .= '/' . urlencode($parameter);
					break;
				case is_array($parameter):
					$parameterArray = array();
					foreach($parameter as $key => $value) {
						if(!is_string($value)) {
							trigger_error("can not render a url with non string elements in an array", E_USER_ERROR);
						}
						if(!is_numeric($key)) {
							trigger_error("can not render a hash", E_USER_ERROR);
						}
						$parameterArray[] = urlencode($value);
					}
					$parameters .= '/' . implode(',', $parameterArray);
					break;
				case is_object($parameter):
					$parameters .= '/' . urlencode(serialize($parameter));
					trigger_error('i will not be able to understand what i am doing here ;) ', E_USER_WARNING);
					break;
				default:
					trigger_error('how should I press that to a path ' . var_export($parameter, true));
			}
			$i++;
		}
		return $parameters;

	}
	protected function get404Action($app)
	{
		$action404 = 'action__notFound';
		if(is_callable(array($app->controller, $action404))) {
			return $action404;
		} else {
			return 'actionDefault';
		}
	}

	/**
	 * is there an alternative one
	 *
	 * @param string $class
	 * @param string $method
	 *
	 * @return Controller\Action
	 */
	protected function getAlternativeHandler($class, $method)
	{
		static $alternatives;
		if(is_null($alternatives)) {
			$alternatives = array();
		}
		$key = $class . $method;
		if(!array_key_exists($key, $alternatives)) {
			$alternatives[$key] = null;
			foreach($this->loadClass($class) as $action) {
				if($action->controllerName != $class && in_array($method, array($action->actionName, $action->actionNameShort))) {
					$alternatives[$key] = $action;
					break;
				}
			}
		}
		return $alternatives[$key];
	}

	protected function getAppControllerWithCallData(AbstractApp $app, $callData)
	{
		if(get_class($app->controller) == $callData['class'] || is_subclass_of($app->controller, $callData['class'])) {
			return $app->controller;
		} else {
			$controllerClass = $callData['class'];
			return new $controllerClass($app);
		}
	}
	public function control(AbstractApp $app)
	{
		$callData = $this->getCallData($app);
		if($callData) {
			$appController = $this->getAppControllerWithCallData($app, $callData);
			if($appController != $app->controller && $appController instanceof MVC\Controller\AbstractAction) {
				$methodName = 'run';
			} else {
				$methodName = $callData['action'];
			}
			$method = array($appController, $methodName);
			$this->lastAction = $callData['action'];
			$this->lastParameters = $callData['parms'];
			if(!is_callable($method)) {
				if($callData['action'] != 'actionDefault') {
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
		} else if($parameter->type == 'array' && !is_array($value)) {
			if(empty($value)) {
				$value = array();
			} else {
				$value = array($value);
			}
		}
		return $value;
	}

	/**
	 * Renders a link that will trigger a method on a given controller class name with the given parameters
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
			// strip "action" from the method name
			if (strpos($methodName, 'action') === 0) {
				$methodName = strtolower(substr($methodName, 6, 1)) . substr($methodName, 7);
			}
			// start with base
			$ret = $this->baseURL;
			// concatenate with the instance information
			if(self::$exposeClassId) {
				$ret .= '/' . urlencode($this->getControllerId());
			}
			// add the (stripped) method name
			if(!empty($methodName)) {
				$ret .= '/' . urlencode($methodName);
			}
		}
		// append parameters
		$ret .= self::renderPathParameters($parameters, count($parameters));
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
			/* @var $action Controller\Action */
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