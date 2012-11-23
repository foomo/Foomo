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
abstract class AbstractBaseTest extends Persistence\Queryable\AbstractTest {

	protected $setupWasSuccessful = false;
	
	public function setUp() {
		$domainConfig = \Foomo\Config::getConf(\Foomo\Module::NAME, \Foomo\Cache\Test\DomainConfig::NAME);
		if($domainConfig) {
			$fastPersistorConf = $domainConfig->fastPersistors['memcached'];
			$queryablePersistorConf = $domainConfig->queryablePersistors['pdo'];
			$fastPersistor = \Foomo\Cache\Manager::getPersistorFromConf($fastPersistorConf, false);
			$pdoPersistor = \Foomo\Cache\Manager::getPersistorFromConf($queryablePersistorConf, true);
			if(is_object($fastPersistor) && is_object($pdoPersistor)) {
				$this->saveManagerSettings();
				$this->clearMockCache($pdoPersistor, $fastPersistor);
				Manager::initialize($pdoPersistor, $fastPersistor);
				\ob_start();
				//Manager::reset(null, true, false);
				\ob_end_clean();
				$this->setupWasSuccessful = true;
			} else {
				$this->markTestSkipped('configuration ' . \Foomo\Cache\Test\DomainConfig::NAME . ' for module ' . \Foomo\Module::NAME . ' seems invalid - I want a pdo and a memcached persistor');
				$this->setupWasSuccessful = false;
			}
		} else {
			$this->setupWasSuccessful = false;
		}
		if(!$this->setupWasSuccessful) {
			$this->markTestSkipped('missing configuration ' . \Foomo\Cache\Test\DomainConfig::NAME . ' for module ' . \Foomo\Module::NAME);
		}
	}
	public function tearDown() {
		if($this->setupWasSuccessful) {
			$this->restoreManagerSettings();
		}
	}


}
