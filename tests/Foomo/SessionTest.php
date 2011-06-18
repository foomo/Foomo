<?php

namespace Foomo;

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
	 * @expectedException Exception
	 */
	public function testWriteToReadOnlySession()
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