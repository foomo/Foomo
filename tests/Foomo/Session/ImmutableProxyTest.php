<?php

namespace Foomo\Session;

class ImmutableProxyTest extends \PHPUnit_Framework_TestCase {
	public function setUp()
	{
		if(!\Foomo\Session::getEnabled()) {
			$this->markTestSkipped('session is not enabled');
		}
	}
	/**
	 * @expectedException Exception
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
	public function testWrongReadAccess()
	{
		// we have a testing problem here ...
		$mock = new TestMockClass;
		$immutableProxy = new ImmutableProxy($mock);
		$this->assertEquals(null, $immutableProxy->bar);
	}
}
