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

use Foomo\Yaml;

/**
 * test if symfonys yaml parser is friendly
 *
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
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