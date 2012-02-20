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

/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
class SessionTest extends \PHPUnit_Framework_TestCase {
	public function setUp()
	{
		if(!Session::getEnabled()) {
			$this->markTestSkipped('session must be enabled / configured');
		} else {
			Session::destroy();
		}
	}
	/**
	 * this test is disabled until we have sth. to make the immutable proxy 
	 * get_class safe like __get_class()
	 * @expectedException Exception
	 */
	public function disabledtestWriteToReadOnlySession()
	{
		Session::lockAndLoad();
		Session::getClassInstance('Foomo\\Session\\TestMockClass');
		Session::saveAndRelease();
		Session::getClassInstance('Foomo\\Session\\TestMockClass')->instanceId = 'fizz';
	}
	public function testCallReadOnlyObject()
	{
		Session::lockAndLoad();
		Session::getClassInstance('Foomo\\Session\\TestMockClass');
		Session::saveAndRelease();
		$this->assertEquals(
			'ab',
			Session::getClassInstance('Foomo\\Session\\TestMockClass')->foo('a', 'b')
		);
	}

	/*
	 * @expectedException Exception
	public function testGetInstanceWhenNotLocked()
	{
		// var_dump(Session::getInstance());
		Session::getClassInstance('Foomo\\Session\\TestMockClass')->instanceId = 'foobar';
	}
	*/
	public function testGetInstance()
	{
		Session::lockAndLoad();
		$first = Session::getClassInstance('Foomo\\Session\\TestMockClass');
		$second = Session::getClassInstance('Foomo\\Session\\TestMockClass');
		$this->assertTrue($first === $second);
	}
	public function testSetClassInstance()
	{
		Session::lockAndLoad();
		$new = new Session\TestMockClass();
		Session::setClassInstance($new);
		$inst = Session::getClassInstance('Foomo\\Session\\TestMockClass');
		$this->assertEquals($inst->instanceId, Session::getClassInstance('Foomo\\Session\\TestMockClass')->instanceId);
	}
	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testSetClassInstanceInvalidArg()
	{
		Session::lockAndLoad();
		Session::setClassInstance('Take that');
	}
	public function testUnsetClassInstance()
	{
		Session::lockAndLoad();
		$first = Session::getClassInstance('Foomo\\Session\\TestMockClass');
		Session::unsetClassInstance('Foomo\\Session\\TestMockClass');
		$this->assertFalse(Session::classInstanceIsset('Foomo\\Session\\TestMockClass'));
		$this->assertTrue(Session::getClassInstance('Foomo\\Session\\TestMockClass')->instanceId > $first->instanceId);
	}
}