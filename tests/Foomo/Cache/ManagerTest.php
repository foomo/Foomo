<?php

namespace Foomo\Cache;

use Foomo\Cache\Persistence\Expr;
use Foomo\Timer;

class ManagerTest extends AbstractBaseTest {

	private $resource;
	private $arguments;
	private $method;
	private $className;
	private $object;

	public function setUp() {
		parent::setUp();

		$this->className = 'Foomo\Cache\MockObjects\SampleResources';
		$this->object = new $this->className;
		$this->method = 'getHoroscopeData';
		$this->arguments = array(0, 'myLocation');
		$this->resource = \Foomo\Cache\Proxy::getEmptyResource($this->className, $this->method, $this->arguments);
		$this->resource->value = \call_user_func_array(array($this->object, $this->method), $this->arguments);
	}

	public function testLoadSaveDelete() {
		Manager::save($this->resource);
		$loadedResource = Manager::load($this->resource);
		$this->assertEquals($this->resource, $loadedResource);
		$success = Manager::delete($this->resource);
		$this->assertEquals(true, $success);
		$loadedResource = Manager::load($this->resource);
		$this->assertEquals(null, $loadedResource, 'Resources were not deleted after call to delete.');
	}

	public function testQuery() {
		Timer::addMarker('test query');
		$this->storeTestResources();
		/*
		 * @var \Foomo\Cache\Persistence\CacheQuery $query
		 */

		$expr = Expr::groupAnd(
						Expr::idEq($this->resource->id),
						Expr::isNotExpired(),
						Expr::statusValid()
		);

		$resultIterator = Manager::query($this->resource->name, $expr, 1, 0);

		foreach ($resultIterator as $foundResource) {
			$this->assertEquals($this->resource->name, $foundResource->name);
			$this->assertEquals($this->resource->id, $foundResource->id);
			$this->assertEquals(CacheResource::STATUS_VALID, $foundResource->status);
		}

		$this->assertEquals(1, count($resultIterator));
	}

	private function storeTestResources() {
		$argumentCombinations = array(array(0, 'myLocation'), array(1, 'myLocation1'), array(2, 'myLocation2'), array(3, 'myLocation3'));
		foreach ($argumentCombinations as $arguments) {
			$this->resource = \Foomo\Cache\Proxy::getEmptyResource($this->className, $this->method, $arguments);
			$this->resource->value = call_user_func_array(array($this->object, $this->method), $arguments);
			Manager::save($this->resource);
		}
	}
	
	
	
}