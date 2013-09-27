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
class PathTest extends TestCase
{
	public $command;
	public $parameters;

	/**
	 * @param string $path
	 * @return Path
	 */
	private function getPath($path)
	{
		return new Path($path);
	}
	public function testOnlyMethod()
	{
		$path = $this->getPath('/foo');
		$this->assertEquals($path->command, 'foo');
		$this->assertCount(0, $path->parameters);
	}
	public function testMethodWithAParm()
	{
		$path = $this->getPath('/foo/:bla');
		$this->assertEquals($path->command, 'foo');
		$this->assertCount(1, $path->parameters);
		$this->assertEquals(array('bla' => array('prefix' => '', 'postfix' => '')), $path->parameters);
	}
	public function testMethodWithTwoParms()
	{
		$path = $this->getPath('/bar/:bla/:blubb');
		$this->assertEquals($path->command, 'bar');
		$this->assertCount(2, $path->parameters);
		$this->assertEquals(array('bla' => array('prefix' => '', 'postfix' => ''), 'blubb' => array('prefix' => '', 'postfix' => '')), $path->parameters);
	}
	public function testMethodWithTwoPrefixedParms()
	{
		$path = $this->getPath('/bar/a:bla/b:blubb');
		$this->assertEquals($path->command, 'bar');
		$this->assertCount(2, $path->parameters);
		$this->assertEquals(array('bla' => array('prefix' => 'a', 'postfix' => ''), 'blubb' => array('prefix' => 'b', 'postfix' => '')), $path->parameters);
	}

	public function testMethodWithTwoPostfixedParms()
	{
		'/bar/a-BLA_WERT/b-BLUBB_WERT.txt';
		$path = $this->getPath('/bar/a-:bla/b-:blubb:.txt');
		$path = $this->getPath('/bar/a:bla/b:blubb:.txt');
		$this->assertEquals($path->command, 'bar');
		$this->assertCount(2, $path->parameters);
		$this->assertEquals(array('bla' => array('prefix' => 'a', 'postfix' => ''), 'blubb' => array('prefix' => 'b', 'postfix' => '.txt')), $path->parameters);
	}


	public function testDefault()
	{
		$path = $this->getPath('*');
		$this->assertEquals($path->command, '*');
		$this->assertCount(0, $path->parameters);
		$this->assertTrue($path->matches('/hjhjkhjkhjk'));
		$this->assertTrue($path->matches('/*/jjjj'));
		$this->assertTrue($path->matches(''));
		$this->assertTrue($path->matches('foo'));
		$this->assertTrue($path->matches('bar/'));
	}

	public function testNoCommand()
	{
		$path = $this->getPath('/');
		$this->assertEquals($path->command, '/');
		$this->assertCount(0, $path->parameters);
		$this->assertTrue($path->matches('/'));
		$this->assertFalse($path->matches('/*/jjjj'));
		$this->assertFalse($path->matches(''));
		$this->assertFalse($path->matches('foo'));
		$this->assertFalse($path->matches('bar/'));

	}

	public function testMatchesMethodOnly()
	{
		$this->assertTrue($this->getPath('/foo')->matches('/foo'));
		$this->assertFalse($this->getPath('/foo')->matches('/bar'));
	}

	public function testMatchesMethodWithParameters()
	{
		$this->assertTrue($this->getPath('/foo/:bar/:blubb')->matches('/foo/1/2'));
		$this->assertFalse($this->getPath('/foo/:bar/:blubb')->matches('/foo/1/2/3'));
	}

	public function testMatchesMethodWithPrefixedParameters()
	{
		$this->assertTrue($this->getPath('/foo/a-:bar/b-:blubb')->matches('/foo/a-1/b-2'));
		$this->assertFalse($this->getPath('/foo/a-:bar/b-:blubb')->matches('/foo/a1/b2'));
	}

	public function testExtractParameters()
	{
		$this->assertEquals(array('bar' => '1', 'blubb' => '2'), $this->getPath('/foo/:bar/:blubb')->extractParameters('/foo/1/2'));
		$this->assertEquals(array('bar' => '1', 'blubb' => '2'), $this->getPath('/foo/a-:bar/b:blubb')->extractParameters('/foo/a-1/b2'));
	}
}