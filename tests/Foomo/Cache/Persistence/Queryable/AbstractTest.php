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

namespace Foomo\Cache\Persistence\Queryable;

use Foomo\Cache\Proxy;

/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
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
		if($fastPersistor) {
			$fastPersistor->reset();
		}
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