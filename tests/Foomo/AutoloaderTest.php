<?php

namespace Foomo;

class AutoloaderTest extends \PHPUnit_Framework_TestCase {
	public function testGetClassFileName()
	{
		$this->assertEquals(realpath(__FILE__), realpath(AutoLoader::getClassFileName(__CLASS__)));
	}
	public function testClassMap()
	{
		$classMap = AutoLoader::getClassMap();
		$this->assertEquals(realpath(__FILE__), $classMap[__CLASS__]);
	}
	public function testGetClassesByFileName()
	{
		$this->assertEquals(array(__CLASS__), AutoLoader::getClassesByFileName(realpath(__FILE__)));
	}
	public function testBuildClassMap()
	{
		// the main part is to actually run the scanning code
		$autoLoader = AutoLoader::getInstance();
		$classMap = $autoLoader->buildClassMap(true);
		$this->assertTrue(array_key_exists(__CLASS__, $classMap));
	}
	public function testLoadClassesInDir()
	{
		$dirClasses = array(
			'Foomo\\Autoloader\\Mock\\Bar\\Bla',
			'Foomo\\Autoloader\\Mock\\Foo\Blubb'
		);
		Autoloader::loadClassesInDir(realpath(__DIR__));
		foreach($dirClasses as $dirClass) {
			$this->assertTrue(class_exists($dirClass, false));
		}
	}
}