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

use Foomo\Timer;
/**
 * a router
 *
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
use Foomo\Router;
use Foomo\MVC;
use Foomo\MVC\AbstractApp;

class AppRunner
{
	public static function run(AbstractApp $app, Router $router, $baseURL = null, $forceBaseURL = null, $forceNoHTMLDocument = false)
	{
		Timer::start($timerTopic = 'app runner prepare');
		$handler = MVC::prepare($app, $baseURL, $forceBaseURL, __NAMESPACE__ . '\\URLHandler');
		Timer::stop($timerTopic);
		$handler->router = $router;
		Timer::start($timerTopic = 'app runner execute');
		$exception = MVC::execute($app, $handler);
		Timer::stop($timerTopic);
		Timer::start($timerTopic = 'app runner render');
		$ret = MVC::render($app, $handler, $exception, $forceNoHTMLDocument);
		Timer::stop($timerTopic);
		return $ret;
	}
}