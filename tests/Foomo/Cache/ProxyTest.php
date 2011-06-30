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

namespace Foomo\Cache;

/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
class ProxyTest extends AbstractBaseTest {

	const MOCK_CLASS_NAME = 'Foomo\\Cache\\MockObjects\\SampleResources';

	public function testGetEmptyResourceStatic() {
		$resource = Proxy::getEmptyResource($className = self::MOCK_CLASS_NAME, $method = 'test', $arguments = array('fooVal', 'barVal'));
		$this->assertNotNull($resource);
		$this->assertEquals($className . '::' . $method, $resource->name);
		$this->assertProperties($resource, $arguments);
	}

	public function testGetEmptyResourceNonStatic() {
		$className = self::MOCK_CLASS_NAME;
		$obj = new $className;
		$resource = Proxy::getEmptyResource($obj, $method = 'testNonStatic', $arguments = array('fooVal', 'barVal'));
		$this->assertNotNull($resource);
		$this->assertEquals($className . '->' . $method, $resource->name);
		$this->assertProperties($resource, $arguments);
	}

	private function assertProperties($resource, $arguments) {
		$this->assertEquals(
				array(
					'foo' => $arguments[0],
					'bar' => $arguments[1]
				),
				$resource->properties
		);
	}

	public function testCallStatic() {
		$result = Proxy::call(self::MOCK_CLASS_NAME, 'test', array('a', 'b'));
		$this->assertEquals($result, 'foo: a, bar: b');
	}

}