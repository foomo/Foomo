<?php

namespace Foomo\Cache;

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