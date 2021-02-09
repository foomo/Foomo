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

use Foomo\Router\MockRouter;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * a router
 *
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
class RouterTest extends TestCase
{
	public function testRouter()
	{
		$router = Router::getRouter()
			->addRoutes(array(
				'/foo/super:bar/test-:foo' => array($this, 'doTheSepp'),
				'/foo/:bar' => array($this, 'doTheSepp'),
				'/static/:test' => array(__CLASS__, 'staticTest'),
				'*' => array($this, 'doTheSepp')
			))
		;
		$this->assertEquals($this->doTheSepp('foo', 'bar'), $router->execute('/foo/superbar/test-foo'));
		$this->assertEquals($this->doTheSepp('foo', 'bar'), $router->execute('/foo/superbar/test-foo?a=b'));
		$this->assertEquals($this->doTheSepp(null, 'bar'), $router->execute('/foo/bar'));
		$this->assertEquals(self::staticTest('static'), $router->execute('/static/static'));
		$this->assertEquals($this->doTheSepp(null, null), $router->execute('/bla'));
	}
	public function testCustomRouter()
	{
		$mockRouter = new MockRouter();
		$this->assertEquals('login', $mockRouter->execute('/login'));
		$this->assertEquals('default', $mockRouter->execute('/fsdfsd'));
	}
	public function testUrl()
	{
		$router = Router::getRouter()
			->addRoutes(array(
				'/foo/super:bar/test-:foo' => array($this, 'doTheSepp'),
				'/foo/:bar' => array($this, 'doTheSepp'),
				'/static/:test' => array(__CLASS__, 'staticTest'),
				'*' => array($this, 'doTheSepp')
			))
		;
		$this->assertEquals('/foo/super2/test-1', $router->url(__CLASS__, 'foo', array('1', '2')));
		$this->assertEquals('/static/sepp', $router->url(__CLASS__, 'static', array('sepp')));
	}
	public function testCanRoute()
	{
		$router = Router::getRouter()
			->addRoutes(array(
				'/foo/super:bar/test-:foo' => array($this, 'doTheSepp'),
				'/foo/:bar' => array($this, 'doTheSepp'),
				'/static/:test' => array(__CLASS__, 'staticTest')
			))
		;
		$this->assertInstanceOf('Foomo\\Router\\Route', $router->canRoute('/foo/superbar/test-foo'));
		$this->assertInstanceOf('Foomo\\Router\\Route', $router->canRoute('/static/test'));
		$this->assertNull($router->canRoute('/bla'));

	}
	public static function staticTest($test)
	{
		return 'static-' . $test;
	}
	public function doTheSepp($foo = null, $bar = null)
	{
		return 'theSepp:' . $foo . $bar;
	}
}