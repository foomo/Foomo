<?php

namespace Foomo\Cache;

class InvalidatorTest extends AbstractBaseTest {

	/**
	 *
	 * @var DependencyModel
	 */
	private $array_tree;
	private $invalidator;
	private $resource;
	private $arguments;
	private $method;
	private $className;
	private $dependencyModel;

	public function setUp() {
		parent::setUp();
		$this->invalidator = new Invalidator();
		$this->className = 'Foomo\Cache\MockObjects\SampleResources';
		$this->method = 'getHoroscopeData';
		$this->arguments = array(0, 'myLocation');
		$this->resource = Proxy::getEmptyResource($this->className, $this->method, $this->arguments);
		$this->dependencyModel = DependencyModel::getInstance();
	}

	/**
	 * check three invalidation policies are working
	 */
	public function testInvalidate() {

		//call dependent methods first
		$object = new $this->className;

		Proxy::call($object, 'renderHoroscope', array(0, 'myLocation', 'myTemplate'), Invalidator::POLICY_DO_NOTHING);
		Proxy::call($object, 'renderHoroscope', array(0, 'myLocation', 'myTemplate1'), Invalidator::POLICY_DO_NOTHING);
		Proxy::call($object, 'renderHoroscope', array(0, 'myLocation', 'myTemplate2'), Invalidator::POLICY_DO_NOTHING);
		Proxy::call($object, 'renderHoroscope', array(1, 'myLocation1', 'myTemplate1'), Invalidator::POLICY_DO_NOTHING);

		//call base method
		$result = Proxy::call($object, $this->method, $this->arguments, Invalidator::POLICY_DO_NOTHING);

		$this->resource->value = $result;
		$this->resource->invalidationPolicy = Invalidator::POLICY_INVALIDATE;
		$numBerOfInvalidatedResources = $this->invalidator->invalidate($this->resource);

		$this->assertEquals(3, $numBerOfInvalidatedResources);

		$this->resource = Proxy::getEmptyResource($object, $this->method, $this->arguments);

		//check all have been invalidated
		$listOfDependentResourceNames = $this->dependencyModel->getDependencyList($this->resource->name);
		foreach ($listOfDependentResourceNames as $dependentResourceName) {
			//find all method calls
			$expr = \Foomo\Cache\Persistence\Expr::propsEq($this->resource->properties);

			$dependentResources = \Foomo\Cache\Manager::query($dependentResourceName, $expr);
			foreach ($dependentResources as $dependentResource) {
				if ($dependentResource->id == $this->resource->id)
					continue;
				if ($dependentResource)
					$this->assertEquals(CacheResource::STATUS_INVALID, $dependentResource->status);
			}
		}


		// now call invalidator with REBUILD policy

		$this->resource = Proxy::getEmptyResource($object, $this->method, $this->arguments);
		$this->resource->invalidationPolicy = Invalidator::POLICY_INSTANT_REBUILD;
		$numBerOfInvalidatedResources = $this->invalidator->invalidate($this->resource);
		$this->assertEquals(3, $numBerOfInvalidatedResources);

		$this->resource = Proxy::getEmptyResource($object, $this->method, $this->arguments);

		//check all have been invalidated
		$listOfDependentResourceNames = $this->dependencyModel->getDependencyList($this->resource->name);
		foreach ($listOfDependentResourceNames as $dependentResourceName) {
			//find all method calls
			//find all method calls
			$expr = \Foomo\Cache\Persistence\Expr::propsEq($this->resource->properties);

			$dependentResources = \Foomo\Cache\Manager::query($dependentResourceName, $expr);

			foreach ($dependentResources as $dependentResource) {
				//var_dump($dependentResource);
				if ($dependentResource->id == $this->resource->id)
					continue;
				if ($dependentResource)
					$this->assertEquals(CacheResource::STATUS_VALID, $dependentResource->status);
			}
		}



// now call invalidator with DELETE policy

		$this->resource = Proxy::getEmptyResource($object, $this->method, $this->arguments);
		$this->resource->invalidationPolicy = Invalidator::POLICY_DELETE;
		$numBerOfInvalidatedResources = $this->invalidator->invalidate($this->resource);
		$this->assertEquals(3, $numBerOfInvalidatedResources);

		$this->resource = Proxy::getEmptyResource($object, $this->method, $this->arguments);

		//check all have been deleted

		$cnt = 0;
		$listOfDependentResourceNames = $this->dependencyModel->getDependencyList($this->resource->name);
		foreach ($listOfDependentResourceNames as $dependentResourceName) {
			//find all method calls
			$expr = \Foomo\Cache\Persistence\Expr::propsEq($this->resource->properties);
			$dependentResources = \Foomo\Cache\Manager::query($dependentResourceName, $expr);
			foreach ($dependentResources as $dependentResource) {
				if ($dependentResource->id == $this->resource->id)
					continue;
				if ($dependentResource) {
					$cnt++;
				}
			}
		}
		$this->assertEquals(0, $cnt, 'Cached object not deleted during invalidation when policy DELETE');


//check the root resource is still there
		$this->resource = Manager::load($this->resource);
		$this->assertNotNull($this->resource);
	}

}