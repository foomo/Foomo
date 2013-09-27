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

namespace Foomo\Router;

use PHPUnit_Framework_TestCase as TestCase;

/**
 * a router
 *
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
class RouteTest extends TestCase
{
	public $command;
	public $parameters;

	/**
	 * @param string $path
	 * @param callback $callback
	 *
	 * @return Route
	 */
	private function getRoute($path, $callback)
	{
		return new Route(new Path($path), $callback);
	}
	public function testGetParametersForCallBack()
	{
		$callback = array($this, 'superSepp');
		$route = $this->getRoute('/foo/:bar/test-:foo', $callback);
		$this->assertEquals(array('foo', 'bar'), $route->getParametersForCallBack($callback, array('bar' => 'bar', 'foo' => 'foo')));
	}
	public function testHandlesMethod()
	{
		$callback = array($this, 'superSepp');
		$route = $this->getRoute('/foo/:bar/test-:foo', $callback);
		$this->assertTrue($route->handlesMethod(__CLASS__, 'foo'));
		$this->assertTrue($route->handlesMethod(__CLASS__, 'superSepp'));
		$this->assertFalse($route->handlesMethod(__CLASS__, 'actionSuperSepp'));
		$this->assertFalse($route->handlesMethod(__CLASS__, 'actiouperSepp'));
	}
	public function testExecute()
	{
		$callback = array($this, 'superSepp');
		$route = $this->getRoute('/foo/:bar/test-:foo', $callback);
		$this->assertEquals('foobar', $route->execute('/foo/bar/test-foo'));
	}
	public function superSepp($foo, $bar)
	{
		return $foo . $bar;
	}
}