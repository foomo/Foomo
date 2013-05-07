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

namespace Foomo\Router\MVC;


/**
 * a router
 *
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
use Foomo\MVC\AbstractApp;
use Foomo\MVC;
use Foomo\Router;
use Foomo\Timer;

class URLHandler extends \Foomo\MVC\URLHandler
{
	/**
	 * @var Router
	 */
	public $router;

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
		if($this->router) {
			$url = $this->router->url($className, $methodName, $parameters);
			if(empty($url)) {
				$alternativeAction = $this->getAlternativeHandler($className, $methodName);
				if($alternativeAction) {
					$url = $this->router->url($alternativeAction->controllerName, 'run', $parameters);
				}
			}
			if(!empty($url)) {
				return $this->baseURL . $url;
			}
		}
		return parent::renderUrl($className, $methodName, $parameters);
	}
	public function control(AbstractApp $app)
	{
		// @todo $this->path not handled ...
		$path = '/' . $this->getCallPath();
		$route = $this->router->canRoute($path);
		if($route) {
			$this->lastAction = $route->getCallbackName();
			$this->lastParameters = $route->getParameters($path);
			if(!$route->isLive()) {
				$callbackClass = $route->callback[0];
				$action = new $callbackClass($app);
				$route->callback[0] = $action;
				$this->lastAction = $action->getActionName();
			}
			return $route->execute($path);
		} else {
			// prevent duplicate handling
			$callData = $this->getCallData($app);
			if ($callData) {
				$method = array($app->controller, $callData['action']);
				if($this->router->isResponsibleFor($method)) {
					$this->lastAction = $this->get404Action($app);
					$this->lastParameters = array();
					return call_user_func_array(array($app->controller, $this->lastAction), array());
				}
			}
			return parent::control($app);
		}
	}
}