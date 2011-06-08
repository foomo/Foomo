<?php

namespace Foomo;

class SessionTest extends \PHPUnit_Framework_TestCase {
	public function testGetInstance()
	{
		$first = Session::getClassInstance('Foomo\\Session\\TestMockClass');
		$second = Session::getClassInstance('Foomo\\Session\\TestMockClass');
		$this->assertTrue($first === $second);
	}
}