<?php

/*
 * bestbytes-copyright-placeholder
 */

namespace Foomo;

use Foomo\Yaml;
/**
 * test if symfonys yaml parser is friendly
 *
 */
class YamlTest extends \PHPUnit_Framework_TestCase {
	public function testParse()
	{
		$yaml = self::getYml('complex.yml');		
		$result = Yaml::parse($yaml);
		$this->assertEquals($result['bill-to']['given'], 'Chris');
	}
	public function testDump()
	{
		$config = array(
			'db' => array(
				'type' => 'mysql',
				'port' => 80,
			),
			'smtp' => array(
				'domain' => 'test.com',
				'haa' => array(0,1,2,3,4)
			)
		);
		$dump = Yaml::dump($config);
		$ymlFile = self::getYml('expectedDumpResult.yml');
		$this->assertEquals($dump, $ymlFile);
	}
	private static function getYml($name)
	{
		return  file_get_contents(dirname(__FILE__) . \DIRECTORY_SEPARATOR . 'yamlResources' . \DIRECTORY_SEPARATOR .  $name );
	}
}