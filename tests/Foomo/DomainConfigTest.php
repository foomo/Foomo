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