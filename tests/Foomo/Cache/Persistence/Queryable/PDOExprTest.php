<?php

namespace Foomo\Cache\Persistence\Queryable;

use Foomo\Cache\Persistence\Expr;
use \Foomo\Cache\Manager;

class PDOExprTest extends \PHPUnit_Framework_TestCase {

	private $persistor;
	private $resource;
	private $arguments;
	private $method;
	private $className;
	private $object;

	public function setUp() {
		$this->className = 'Foomo\Cache\MockObjects\SampleResources';
		$this->object = new $this->className;
		$this->method = 'getHoroscopeData';
		$this->arguments = array(0, 'myLocation');
		$this->resource = \Foomo\Cache\Proxy::getEmptyResource($this->object, $this->method, $this->arguments);
		$this->resource->value = \call_user_func_array(array($this->object, $this->method), $this->arguments);
		$this->persistor = new \Foomo\Cache\Persistence\Queryable\PDOPersistor('mysql://root@localhost/PDOCacheDB');
		Manager::initialize($this->persistor);
		Manager::reset(null, true, false);
	}

	public function testExpr() {
		$this->storeTestResources();

		$expr = Expr::idEq($this->resource->id);
		$iterator = $this->persistor->query($this->resource->name, $expr, 0, 0);
		$this->assertEquals(1, count($iterator));



		$expr = Expr::groupAnd(Expr::idEq($this->resource->id),
						Expr::isExpired()
		);

		$iterator = $this->persistor->query($this->resource->name, $expr, 0, 0);
		$this->assertEquals(0, count($iterator));


		$expr = Expr::groupAnd(Expr::idEq($this->resource->id),
						Expr::isExpired(),
						Expr::statusValid()
		);

		$iterator = $this->persistor->query($this->resource->name, $expr, 0, 0);
		$this->assertEquals(0, count($iterator));


		$expr = Expr::groupAnd(
						Expr::isNotExpired(),
						Expr::statusValid()
		);

		$iterator = $this->persistor->query($this->resource->name, $expr, 0, 0);
		$this->assertEquals(4, count($iterator));



		$expr = Expr::groupOr(
						Expr::idNe($this->resource->id),
						Expr::groupAnd(
								Expr::isNotExpired(),
								Expr::isExpired(),
								Expr::statusValid()
						)
		);

		$iterator = $this->persistor->query($this->resource->name, $expr, 0, 0);
		$this->assertEquals(3, count($iterator));



		$expr = Expr::groupAnd(
						Expr::createdBefore(\time() + 1)
		);

		$iterator = $this->persistor->query($this->resource->name, $expr, 0, 0);
		$this->assertEquals(4, count($iterator));
	}

	public function testPropertiesHandling() {
		$this->storeTestResources();

		$expr = Expr::groupAnd(
						Expr::idEq($this->resource->id),
						Expr::propsEq($this->resource->properties)
		);

		$iterator = $this->persistor->query($this->resource->name, $expr, 0, 0);
		$this->assertEquals(1, count($iterator));


		$expr = Expr::groupAnd(
						Expr::idNe($this->resource->id),
						Expr::propNe('location', 'myLocation')
		);

		$iterator = $this->persistor->query($this->resource->name, $expr, 0, 0);
		$this->assertEquals(3, count($iterator));
	}

	public function testGetCachedResources() {
		$this->storeTestResources();

		$ids = $this->persistor->getListOfCachedResources();
	}

	private function storeTestResources() {
		$argumentCombinations = array(array(0, 'myLocation'), array(1, 'myLocation1'), array(2, 'myLocation2'), array(3, 'myLocation3'));
		foreach ($argumentCombinations as $arguments) {
			$resource = \Foomo\Cache\Proxy::getEmptyResource($this->object, $this->method, $arguments);
			$resource->value = call_user_func_array(array($this->object, $this->method), $arguments);
			Manager::save($resource);
		}
	}

}