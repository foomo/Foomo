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

namespace Foomo;

use Foomo\Router\Route;
use Foomo\Router\RouteMatcherInterface;

/**
 * a router
 *
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
class Router
{
	/**
	 * @var Router\Route[]
	 */
	private $routes = array();
	/**
	 * @var string
	 */
	public $currentPath;
	private $handlingMap = array();
	protected function __construct()
	{
	}
	/**
	 * @return Router
	 */
	public static function getRouter()
	{
		$calledClass = get_called_class();
		return new $calledClass;
	}
	/**
	 * add a route
	 *
	 * @param string $path
	 * @param mixed $callback callable, and reflectable or if extending the router name of a method on $this => convention is handle ...
	 *
	 * @return $this
	 */
	public function route($path, $callback)
	{
		if(is_string($callback) && is_callable(array($this, $callback))) {
			$callback = array($this, $callback);
		}
		$route = Route::createWithPath($path, $callback);
		$this->routes[] = $route;
		return $this;
	}
	public function url($className, $method, $parameters = array())
	{
		$key = $className . $method;
		if(!array_key_exists($key, $this->handlingMap)) {
			$this->handlingMap[$key] = '';
			foreach($this->routes as $route) {
				if($route->handlesMethod($className, $method)) {
					$this->handlingMap[$key] = $route;
					break;
				}
			}
		}
		if(is_object($this->handlingMap[$key])) {
			return $this->handlingMap[$key]->url($parameters);
		} else {
			return '';
		}
	}
	/**
	 * add many routes at once
	 *
	 * @param array $routes path => $callback
	 *
	 * @return $this
	 */
	public function addRoutes(array $routes)
	{
		foreach($routes as $path => $callback) {
			if(is_string($path) && (is_string($callback) || is_array($callback))) {
				$this->route($path, $callback);
			} else if(is_object($callback) && $callback instanceof Route) {
				$this->routes[] = $callback;
			} else {
				trigger_error('can not route that one ...', E_USER_ERROR);
			}
		}
		return $this;
	}
	/**
	 * can your route
	 *
	 * @param string $path
	 *
	 * @return Route
	 */
	public function canRoute($path)
	{
		foreach($this->routes as $route) {
			if($route->matches($path)) {
				return $route;
			}
		}
		return null;
	}

	/**
	 * @param callable $callback
	 * @return bool
	 */
	public function isResponsibleFor($callback)
	{
		foreach($this->routes as $route) {
			if($route->isResponsibleFor($callback)) {
				return true;
			}
		}
		return false;
	}
	public function resolvePath($path)
	{
		foreach($this->routes as $route) {
			if($route->matches($path)) {
				return $route->resolvePath($path);
			}
		}
	}
	/**
	 *
	 * @param string $path
	 *
	 * @return mixed
	 */
	public function execute($path = null)
	{
		if(is_null($path)) {
			if(strpos($_SERVER['REQUEST_URI'], $_SERVER['SCRIPT_NAME']) === 0) {
				$path = substr($_SERVER['REQUEST_URI'], strlen($_SERVER['SCRIPT_NAME']));
			} else {
				$path = $_SERVER['REQUEST_URI'];
			}
		}
		$path = parse_url($path)['path'];
		$this->currentPath = $path;
		foreach($this->routes as $route) {
			if($route->matches($path)) {
				$result = $route->execute($path);
				$this->currentPath = $route->resolvePath($path);
				return $result;
			}
		}
	}
}