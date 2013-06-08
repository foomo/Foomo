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

namespace Foomo\Session;

/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
class ImmutableProxyTest extends \PHPUnit_Framework_TestCase {
	public function setUp()
	{
		if(!\Foomo\Session::getEnabled()) {
			$this->markTestSkipped('session is not enabled');
		}
	}
	/**
	 * @expectedException \LogicException
	 */
	public function testWriteAccessFail()
	{
		$mock = new TestMockClass;
		$immutableProxy = new ImmutableProxy($mock);
		$immutableProxy->test = 3;
	}
	public function testReadAccess()
	{
		$mock = new TestMockClass;
		$immutableProxy = new ImmutableProxy($mock);
		$this->assertEquals($mock->foo, $immutableProxy->foo);
	}
	/**
	 * @expectedException \PHPUnit_Framework_Error_Notice
	 */
	public function testWrongReadAccess()
	{
		$mock = new TestMockClass;
		$immutableProxy = new ImmutableProxy($mock);
		$this->assertEquals(null, $immutableProxy->bar);
	}
	public function tearDown()
	{
		\PHPUnit_Framework_Error_Notice::$enabled = true;
	}

}
