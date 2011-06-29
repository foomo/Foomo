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

namespace Foomo\Cache\Persistence\Fast;

use Foomo\Cache\Manager;

class MemcacheTest extends \Foomo\Cache\AbstractBaseTest {

	private $resource;
	private $arguments;
	private $method;
	private $className;
	private $object;
	private $apcPersistor;

	
	public function testLongId() {
		$memcache = \Foomo\Cache\Manager::getFastPersistor();
		$longId = '12345678901234567890-1234567890234567890-1234567890qwertyuiopsdfghjkl;xcvbnsdfghjkwertyuqwertyuasdfghjklqwertyuioaaaaaaaaaaaaaaaaaaaaaaadddddddddddddddddddddddddddddddddddddddddddddddhjksfgjaksfgahksfgggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggg';
		$resource = \Foomo\Cache\Proxy::getEmptyResource('Foomo\Cache\MockObjects\SampleResources', 'test', array());
		$resource->id = $longId;

		$resource->value = 'I am a value';
		$memcache->save($resource);

		$loaded = $memcache->load($resource);

		$this->assertEquals($resource, $loaded);

	}

}