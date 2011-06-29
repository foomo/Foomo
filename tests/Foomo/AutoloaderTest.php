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