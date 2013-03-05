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
 * class describing a reflected method on a controller or class in general
 *
 * @internal
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
use Foomo\MVC\AbstractApp;

class AbstractAction {
	/**
	 * @var AbstractApp
	 */
	protected $app;
	/**
	 * @var mixed
	 */
	protected $model;
	/**
	 * @var mixed
	 */
	protected $controller;
	public function __construct(AbstractApp $app)
	{
		$this->app = $app;
		$this->model = $this->app->model;
		$this->controller = $this->app->controller;
	}
	public function getActionName()
	{
		$parts = explode('\\', get_called_class());
		return lcfirst(array_pop($parts));
	}
}
