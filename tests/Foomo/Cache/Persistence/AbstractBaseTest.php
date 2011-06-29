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
abstract class AbstractBaseTest extends Persistence\Queryable\AbstractTest{

	public function setUp() {
		$domainConfig = \Foomo\Config::getConf(\Foomo\Module::NAME, \Foomo\Cache\Test\DomainConfig::NAME);
		if($domainConfig) {
			$fastPersistorConf = $domainConfig->fastPersistors['memcached'];
			$queryablePersistorConf = $domainConfig->queryablePersistors['pdo'];
			$fastPersistor = \Foomo\Cache\Manager::getPersistorFromConf($fastPersistorConf, false);
			$pdoPersistor = \Foomo\Cache\Manager::getPersistorFromConf($queryablePersistorConf, true);
			$this->saveManagerSettings();
			$this->clearMockCache($pdoPersistor, $fastPersistor);
			Manager::initialize($pdoPersistor, $fastPersistor);
			\ob_start();
			//Manager::reset(null, true, false);
			\ob_end_clean();
		} else {
			$this->markTestSkipped('missing configuration ' . \Foomo\Cache\Test\DomainConfig::NAME . ' for module ' . \Foomo\Module::NAME);
		}
	}
	
	public function tearDown() {
		$this->restoreManagerSettings();
	}
	
	
}
