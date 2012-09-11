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
 * cache rebuilding job 
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author bostjan <bostjan.marusic@bestbytes.de>
 */
class RebuildJob extends \Foomo\Jobs\AbstractJob
{

	protected $executionRule = '0   0       *       *       *';
	public static $testRun = false;

	/**
	 * @var array
	 */
	protected $invalidationData;

	/**
	 *
	 * @var string 
	 */
	protected $outputFolder;

	/**
	 * get job id
	 * @return string
	 */
	public function getId()
	{
		return sha1(serialize($this->invalidationData));
	}

	public function invalidateResource(CacheResource $resource)
	{
		$this->invalidationData['resource'] = $resource;
		unset($this->invalidationData['resource_name']);
		unset($this->invalidationData['query']);
		return $this;
	}

	/**
	 * @param mixed $classOrObject object/class to call
	 * @param string $method name of the method
	 * @param array $arguments arguments for the call
	 *
	 * 
	 * 
	 */
	public function invalidateCachedProxyCall($classOrObject, $method, $arguments)
	{
		$resource = \Foomo\Cache\Proxy::getEmptyResource($classOrObject, $method, $arguments);
		return $this->invalidateResource($resource);
	}

	/**
	 * 
	 * @param string $resourceName
	 * @param \\Foomo\Cache\Persistence\Expr $query null = all resources with name
	 * @return \Foomo\Cache\RebuildJob
	 */
	public function invalidateWithQuery($resourceName, \Foomo\Cache\Persistence\Expr $query = null)
	{
		$this->invalidationData['resource_name'] = $resourceName;
		$this->invalidationData['query'] = $query;
		unset($this->invalidationData['resource']);
		return $this;
	}

	
	public function run()
	{ 
		if (isset($this->invalidationData['resource'])) {
			$resource = $this->invalidationData['resource'];
			\Foomo\Cache\Manager::invalidate($resource, true, Invalidator::POLICY_INSTANT_REBUILD);
		} else if (isset($this->invalidationData['resource_name'])) {
			$query = null;
			if (isset($this->invalidationData['query'])) {
				$query = $this->invalidationData['query'];
			}
			$resourceName = $this->invalidationData['resource_name'];
			\Foomo\Cache\Manager::invalidateWithQuery($resourceName, $query, true, Invalidator::POLICY_INSTANT_REBUILD);
		} else {
			throw new \RuntimeException('resource to rebuild has not been specified');
		}
	}

}

