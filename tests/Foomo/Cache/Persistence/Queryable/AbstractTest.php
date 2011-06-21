<?php


namespace Foomo\Cache\Persistence\Queryable;

use Foomo\Cache\Proxy;

abstract class AbstractTest extends \PHPUnit_Framework_TestCase {
	protected $fastPersistorBu;
	protected $queryablePersistorBu;
	
	
	protected function saveManagerSettings() {
		$this->fastPersistorBu = \Foomo\Cache\Manager::getFastPersistor();
		$this->queryablePersistorBu = \Foomo\Cache\Manager::getQueryablePersistor();
	}
	
	
	protected function restoreManagerSettings() {
		\FooMo\Cache\Manager::initialize($this->queryablePersistorBu, $this->fastPersistorBu);
	}
	
	
	protected function clearMockCache($queryablePersistor, $fastPersistor) {
		//store the current manager settings
		$fastPersistorBu = \Foomo\Cache\Manager::getFastPersistor();
		$queryablePersistorBu = \Foomo\Cache\Manager::getQueryablePersistor();
		\Foomo\Cache\Manager::initialize($queryablePersistor, null);
		
		//invalidate fast cache
		$fastPersistor->reset();
		//invalidate queryable cache		
		\Foomo\Cache\Manager::invalidateWithQuery('Foomo\Cache\MockObjects\SampleResources->noticeMEEEEEEE', null, true, \Foomo\Cache\Invalidator::POLICY_DELETE);
		\Foomo\Cache\Manager::invalidateWithQuery('Foomo\Cache\MockObjects\SampleResources->iamAmAmixedMethod', null, true, \Foomo\Cache\Invalidator::POLICY_DELETE);
		\Foomo\Cache\Manager::invalidateWithQuery('Foomo\Cache\MockObjects\SampleResources::test', null, true, \Foomo\Cache\Invalidator::POLICY_DELETE);
		\Foomo\Cache\Manager::invalidateWithQuery('Foomo\Cache\MockObjects\SampleResources->testNonStatic', null, true, \Foomo\Cache\Invalidator::POLICY_DELETE);
		\Foomo\Cache\Manager::invalidateWithQuery('Foomo\Cache\MockObjects\SampleResources->getHoroscopeData', null, true, \Foomo\Cache\Invalidator::POLICY_DELETE);
		\Foomo\Cache\Manager::invalidateWithQuery('Foomo\Cache\MockObjects\SampleResources->renderHoroscope', null, true, \Foomo\Cache\Invalidator::POLICY_DELETE);
		\Foomo\Cache\Manager::invalidateWithQuery('Foomo\Cache\MockObjects\SampleResources->renderHoroscope3D', null, true, \Foomo\Cache\Invalidator::POLICY_DELETE);
		\Foomo\Cache\Manager::invalidateWithQuery('Foomo\Cache\MockObjects\SampleResources->sendHosroscopeRendering', null, true, \Foomo\Cache\Invalidator::POLICY_DELETE);
		\Foomo\Cache\Manager::invalidateWithQuery('Foomo\Cache\MockObjects\SampleResources->checkHoroscopeReception', null, true, \Foomo\Cache\Invalidator::POLICY_DELETE);
		\Foomo\Cache\Manager::invalidateWithQuery('Foomo\Cache\MockObjects\SampleResources->checkHoroscopeValid', null, true, \Foomo\Cache\Invalidator::POLICY_DELETE);
		\Foomo\Cache\Manager::invalidateWithQuery('Foomo\Cache\MockObjects\SampleResources->getAddress', null, true, \Foomo\Cache\Invalidator::POLICY_DELETE);
				
		//set manager back to
		\Foomo\Cache\Manager::initialize($queryablePersistorBu, $fastPersistorBu);
	}

}