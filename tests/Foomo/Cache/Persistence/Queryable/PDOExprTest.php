<?php

namespace Foomo\Cache\Persistence\Queryable;

use Foomo\Cache\Persistence\Expr;
use \Foomo\Cache\Manager;

class PDOExprTest extends AbstractTest {

	private $persistor;
	private $resource;
	private $arguments;
	private $method;
	private $className;
	private $object;

	public function setUp() {

		$domainConfig = \Foomo\Config::getConf(\Foomo\Module::NAME, \Foomo\Cache\Test\DomainConfig::NAME);
		if ($domainConfig && !empty($domainConfig->queryablePersistors['pdo'])) {
			$fastPersistorConf = $domainConfig->fastPersistors['memcached'];
			$queryablePersistorConf = $domainConfig->queryablePersistors['pdo'];
			$fastPersistor = \Foomo\Cache\Manager::getPersistorFromConf($fastPersistorConf, false);
			$pdoPersistor = \Foomo\Cache\Manager::getPersistorFromConf($queryablePersistorConf, true);

			$this->className = 'Foomo\Cache\MockObjects\SampleResources';
			$this->object = new $this->className;
			$this->method = 'getHoroscopeData';
			$this->arguments = array(0, 'myLocation');
			$this->resource = \Foomo\Cache\Proxy::getEmptyResource($this->object, $this->method, $this->arguments);
			$this->resource->value = \call_user_func_array(array($this->object, $this->method), $this->arguments);
			$this->persistor = $pdoPersistor;

			$this->saveManagerSettings();
			Manager::initialize($this->persistor);
			$this->clearMockCache($this->persistor, $fastPersistor);
		} else {
			$this->markTestSkipped(
					'missing test config ' . \Foomo\Cache\Test\DomainConfig::NAME .
					' for module ' . \Foomo\Module::NAME . ' respectively the pdo config on it is empty'
			);
		}
	}

	public function tearDown() {
		$this->restoreManagerSettings();
	}

	public function testExpr() {
		$this->storeTestResources();

		$expr = Expr::idEq($this->resource->id);
		$iterator = $this->persistor->query($this->resource->name, $expr, 0, 0);
		$this->assertEquals(1, count($iterator));



		$expr = Expr::groupAnd(Expr::idEq($this->resource->id), Expr::isExpired()
		);

		$iterator = $this->persistor->query($this->resource->name, $expr, 0, 0);
		$this->assertEquals(0, count($iterator));


		$expr = Expr::groupAnd(Expr::idEq($this->resource->id), Expr::isExpired(), Expr::statusValid()
		);

		$iterator = $this->persistor->query($this->resource->name, $expr, 0, 0);
		$this->assertEquals(0, count($iterator));


		$expr = Expr::groupAnd(
						Expr::isNotExpired(), Expr::statusValid()
		);

		$iterator = $this->persistor->query($this->resource->name, $expr, 0, 0);
		$this->assertEquals(4, count($iterator));



		$expr = Expr::groupOr(
						Expr::idNe($this->resource->id), Expr::groupAnd(
								Expr::isNotExpired(), Expr::isExpired(), Expr::statusValid()
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
						Expr::idEq($this->resource->id), Expr::propsEq($this->resource->properties)
		);

		$iterator = $this->persistor->query($this->resource->name, $expr, 0, 0);
		$this->assertEquals(1, count($iterator));


		$expr = Expr::groupAnd(
						Expr::idNe($this->resource->id), Expr::propNe('location', 'myLocation')
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