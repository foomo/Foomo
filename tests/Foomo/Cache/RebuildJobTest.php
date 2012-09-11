<?php

/*
 * This file is part of the foomo Opensource Framework.
 * 
 * The foomo Opensource Framework is free software: you can redistribute it
 * and/or modify it under the terms of the GNU Lesser General Public License as
 * published  by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 * 
 * The foomo Opensource Framework is distributed in the hope that it will
 * be useful, but WITHOUT ANY WARRANTY; without even the implied warranty
 * of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public License along with
 * the foomo Opensource Framework. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Foomo\Cache;

/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author bostjan bostjan.marusic@bestbytes.de
 */
class RebuildJobTest extends AbstractBaseTest
{

	public function testInvalidateResource()
	{
		Manager::save($this->resource);
		sleep(3);
		$loadedResource = Manager::load($this->resource);


		$this->assertGreaterThanOrEqual(3, time() - $loadedResource->creationTime);
		//run rebuilder
		$jobs = \Foomo\Cache\Test\JobList::getJobs();
		\Foomo\Jobs\Runner::runAJob($jobs[0]);

		//check creation time
		$loadedResource1 = Manager::load($this->resource);
		$this->assertLessThanOrEqual(1, time() - $loadedResource1->creationTime, 'resource has not been rebuilt');
	}

	public function testInvalidateProxyCall()
	{
		Manager::save($this->resource);
		sleep(3);
		$loadedResource = Manager::load($this->resource);

		$this->assertGreaterThanOrEqual(3, time() - $loadedResource->creationTime);
		//run rebuilder
		$jobs = \Foomo\Cache\Test\JobList::getJobs();
		\Foomo\Jobs\Runner::runAJob($jobs[1]);

		//check creation time
		$loadedResource1 = Manager::load($this->resource);
		$this->assertLessThanOrEqual(1, time() - $loadedResource1->creationTime, 'resource has not been rebuilt');
	}
	
	public function testInvalidateWithQuery()
	{
		Manager::save($this->resource);
		sleep(3);
		$loadedResource = Manager::load($this->resource);

		$this->assertGreaterThanOrEqual(3, time() - $loadedResource->creationTime);
		//run rebuilder
		$jobs = \Foomo\Cache\Test\JobList::getJobs();
		\Foomo\Jobs\Runner::runAJob($jobs[2]);
		
		//check creation time
		$loadedResource1 = Manager::load($this->resource);
		$this->assertLessThanOrEqual(1, time() - $loadedResource1->creationTime, 'resource has not been rebuilt');
		
		sleep(3);
		
		\Foomo\Jobs\Runner::runAJob($jobs[3]);
		$loadedResource2 = Manager::load($this->resource);
		$this->assertLessThanOrEqual(1, time() - $loadedResource2->creationTime, 'resource has not been rebuilt');
		
		
	}

	public function setUp()
	{
		parent::setUp();
		if ($this->setupWasSuccessful) {
			$this->className = 'Foomo\Cache\MockObjects\SampleResources';
			$this->object = new $this->className;
			$this->method = 'getHoroscopeData';
			$this->arguments = array(0, 'myLocation');
			$this->resource = \Foomo\Cache\Proxy::getEmptyResource($this->className, $this->method, $this->arguments);
			$this->resource->value = \call_user_func_array(array($this->object, $this->method), $this->arguments);
		}
	}

	public function tearDown()
	{
		parent::tearDown();
	}

}

