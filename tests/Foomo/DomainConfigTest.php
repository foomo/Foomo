<?php
namespace Foomo;

use ReflectionClass;

/**
 * test the domain config
 *
 */
class DomainConfigTest extends \PHPUnit_Framework_TestCase {
	public function testDefaultsAndParser()
	{
		$classMap = AutoLoader::getClassMap();
		foreach(array_keys($classMap) as $className) {
			$ref = new ReflectionClass($className);
			if(!$ref->isAbstract() && $ref->isSubclassOf('Foomo\\Config\\AbstractConfig')) {
				/*
				echo '------------------------------------------' . PHP_EOL;
				echo $ref->getName() . ' :' . PHP_EOL;
				echo '------------------------------------------' . PHP_EOL;
				*/
				$config = new $className;
				$dump = Yaml::dump($config->getValue());
				//echo $dump;
				$this->assertEquals(Yaml::parse($dump), $config->getValue(), 'config type ' . $ref->getName() . ' probably gets screwed in the yaml parsers');
				//echo '------------------------------------------' . PHP_EOL;
			}
		}
	}
}