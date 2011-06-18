<?php

namespace Foomo\Cache\Persistence\Queryable;

use Foomo\Cache\Persistence\Expr;

/**
 *
 *
 */
class PDOPersistorTest extends \PHPUnit_Framework_TestCase {

	/**
	 * my persistor
	 *
	 * @var Foomo\Cache\Persistence\Queryable\PDOPersistor
	 */
	private $pdoPersistor;
	private $resource;
	private $arguments;
	private $method;
	private $className;
	private $object;

	public function setUp() {
		$domainConfig = \Foomo\Config::getConf(\Foomo\Module::NAME, \Foomo\Cache\Test\DomainConfig::NAME);
		if($domainConfig && !empty($domainConfig->queryablePersistors['pdo'])) {
			$fastPersistorConf = $domainConfig->fastPersistors['memcached'];
			$queryablePersistorConf = $domainConfig->queryablePersistors['pdo'];

			$fastPersistor = \Foomo\Cache\Manager::getPersistorFromConf($fastPersistorConf, false);
			$pdoPersistor = \Foomo\Cache\Manager::getPersistorFromConf($queryablePersistorConf, true);
			$this->className = 'Foomo\Cache\MockObjects\SampleResources';
			$this->object = new $this->className;
			$this->method = 'getHoroscopeData';
			$this->arguments = array(0, 'myLocation');
			$this->resource = \Foomo\Cache\Proxy::getEmptyResource($this->className, $this->method, $this->arguments);
			$this->resource->value = call_user_func_array(array($this->object, $this->method), $this->arguments);
			$this->pdoPersistor = $pdoPersistor;

			$fastPersistor->reset();
			$this->pdoPersistor->reset(null, true, false);
			\Foomo\Cache\Manager::initialize($this->pdoPersistor, $fastPersistor);
		} else {
			$this->markTestSkipped(
				'missing test config ' . \Foomo\Cache\Test\DomainConfig::NAME . 
				' for module ' . \Foomo\Module::NAME . ' respectively the pdo config on it is empty'
			);
		}
	}

	public function testConnect() {
		$this->assertNotNull($this->pdoPersistor->dbh);
	}

	public function testLoadSaveDelete() {
		$this->pdoPersistor->save($this->resource);
		$loadedResource = $this->pdoPersistor->load($this->resource);
		$this->assertEquals($this->resource, $loadedResource);
		$success = $this->pdoPersistor->delete($this->resource);
		$this->assertEquals(true, $success);
		$loadedResource = $this->pdoPersistor->load($this->resource);
		$this->assertEquals(null, $loadedResource, 'Resources were not deleted after call to delete.');
	}

	public function testqueryWithExpression() {
		$this->storeTestResources();
		$expr = Expr::idEq($this->resource->id);
		$iterator = $this->pdoPersistor->query($this->resource->name, $expr, 0, 0);
		$this->assertEquals(1, count($iterator));



		$expr = Expr::groupAnd(Expr::idEq($this->resource->id),
						Expr::isExpired()
		);

		$iterator = $this->pdoPersistor->query($this->resource->name, $expr, 0, 0);
		$this->assertEquals(0, count($iterator));


		$expr = Expr::groupAnd(Expr::idEq($this->resource->id),
						Expr::isExpired(),
						Expr::statusValid()
		);

		$iterator = $this->pdoPersistor->query($this->resource->name, $expr, 0, 0);
		$this->assertEquals(0, count($iterator));


		$expr = Expr::groupAnd(
						Expr::isNotExpired(),
						Expr::statusValid()
		);
		$iterator = $this->pdoPersistor->query($this->resource->name, $expr, 0, 0);
		$this->assertEquals(4, count($iterator));



		$expr = Expr::groupOr(
						Expr::idNe($this->resource->id),
						Expr::groupAnd(
								Expr::isNotExpired(),
								Expr::isExpired(),
								Expr::statusValid()
						)
		);

		$iterator = $this->pdoPersistor->query($this->resource->name, $expr, 0, 0);
		$this->assertEquals(3, count($iterator));



		$expr = Expr::groupAnd(
						Expr::createdBefore(\time() + 1)
		);

		$iterator = $this->pdoPersistor->query($this->resource->name, $expr, 0, 0);
		$this->assertEquals(4, count($iterator));

	}

	public function testNoticeMeFunction() {
		$obj = new \Foomo\Cache\MockObjects\SampleResources;
		\Foomo\Cache\Proxy::call($obj, 'noticeMEEEEEEE', array(1, 'loc', 'temp', (double) 100));
	}

	public function testMixed() {
		$obj = new \Foomo\Cache\MockObjects\SampleResources();
		$emptyResource = \Foomo\Cache\Proxy::getEmptyResource('Foomo\Cache\MockObjects\SampleResources', 'iamAmAmixedMethod', array('this is a mixed argument'));
		$val = \Foomo\Cache\Proxy::call($obj, 'iamAmAmixedMethod', array('this is a mixed argument'));
		$resources = \Foomo\Cache\Manager::query($emptyResource->name);
		$this->assertEquals(1, count($resources));



		//will not work anymore, because it is serialized as an object
		$resources = \Foomo\Cache\Manager::query($emptyResource->name, Expr::propsEq(array('param' => 'this is a mixed argument')));
		$this->assertEquals(1, count($resources));
	}

	public function testDelete() {
		$this->storeTestResources();

		$this->resource = \Foomo\Cache\Proxy::getEmptyResource($this->className, $this->method, $this->arguments);

		$resources = \Foomo\Cache\Manager::query($this->resource->name);
		//now delete all
		foreach ($resources as $resource) {
			$this->pdoPersistor->delete($resource);
		}
		$resources = \Foomo\Cache\Manager::query($this->resource->name);
		$this->assertEquals(0, count($resources));
	}

	private function storeTestResources() {
		$argumentCombinations = array(array(0, 'myLocation'), array(1, 'myLocation1'), array(2, 'myLocation2'), array(3, 'myLocation3'));
		foreach ($argumentCombinations as $arguments) {
			$resource = \Foomo\Cache\Proxy::getEmptyResource($this->object, $this->method, $arguments);
			$resource->value = call_user_func_array(array($this->object, $this->method), $arguments);
			$this->pdoPersistor->save($resource);
		}
	}
}