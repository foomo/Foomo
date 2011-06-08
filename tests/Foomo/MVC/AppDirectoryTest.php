<?php

namespace Foomo\MVC;

class AppDirectoryTest extends \PHPUnit_Framework_TestCase {
	public function testResolveAppNotNamed()
	{
		$appId = 'Foomo.TestRunner.Frontend';
		$className = 'Foomo\\TestRunner\\Frontend';
		$this->assertEquals($className, AppDirectory::resolveClass($appId), 'could not resolve ' . $appId . ' to ' . $className);
	}
	public function testResolveAppNamed()
	{
		$appId = 'Foomo.toolbox';
		$className = 'Foomo\\Frontend';
		$this->assertEquals($className, AppDirectory::resolveClass($appId), 'could not resolve ' . $appId . ' to ' . $className);
	}
	
}