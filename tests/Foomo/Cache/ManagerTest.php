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

use Foomo\Cache\Persistence\Expr;
use Foomo\Timer;

/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
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