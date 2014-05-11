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
 * discovers dependencied among resources as defined by CacheResourceDescription method annotations
 * 
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
class DependencyModel {

	private static $inst;

	private function __construct()
	{
		self::$inst = $this;
	}

	/**
	 * singleton
	 *
	 * @return \Foomo\Cache\DependencyModel
	 */
	public static function getInstance()
	{
		if (!self::$inst) {
			new self;
		}
		return self::$inst;
	}

	/**
	 * generates an array representation of dependencies
	 *
	 * @param string $resourceName
	 *
	 * @return array nested array of resourceNames
	 */
	public function getDependencyTree($resourceName)
	{
		$dependencyTree = array();
		$dependencyTree[$resourceName] = $this->walkDependencyTree($resourceName, 0);
		return $dependencyTree;
	}

	/**
	 * build a list of dependent resources - linear dependency tree
	 *
	 * @param string $resourceName
	 *
	 * @return string[] array of resource names
	 */
	public function getDependencyList($resourceName)
	{
		$dependencyList = array();
		$dependencyList[] = $resourceName;
		$this->walkDependencyList($resourceName, $dependencyList);
		return $dependencyList;
	}

	/**
	 * list all cacheable resources
	 *
	 * @param bool $invalidateDependencyModelCache invalidate the cache before returning, false default.
	 *
	 * @return mixed|null array('resourceName' => CacheResourceReflection)
	 */
	public function getDirectory($invalidateDependencyModelCache = false)
	{
		static $ret;
		if ($invalidateDependencyModelCache === true) {
			$ret = null;
			$emptyResource = Proxy::getEmptyResource($this, 'cachedGetDirectory', array());
			//Manager::invalidate($emptyResource, true, Invalidator::POLICY_DELETE);
			//@todo an invalidate should be enough ...
			Manager::reset($emptyResource->name);
		}
		if (!isset($ret)) {
			$ret = Proxy::call($this, 'cachedGetDirectory');
		}
		return $ret;
	}

	/**
	 * @Foomo\Cache\CacheResourceDescription('dependencies'='Foomo\AutoLoader::cachedGetClassMap')
	 */
	public function cachedGetDirectory()
	{
		$ret = array();
		$classMap = \Foomo\AutoLoader::getClassMap();
		foreach ($classMap as $className => $file) {
			if ($this->containsCacheAnnotation(\file_get_contents($file))) {
				$classReflection = new \ReflectionClass($className);
				if (!$classReflection->isAbstract() && !$classReflection->isInterface()) {
					foreach ($classReflection->getMethods() as $methodReflection) {
						/* @var $methodReflection \ReflectionMethod */
						if ($this->containsCacheAnnotation($methodReflection->getDocComment())) {
							$reflection = Reflection\CacheResourceReflection::internalGetReflection(
								$classReflection->getName(),
								$methodReflection->getName()
							);
							if ($reflection) {
								$ret[$reflection->resourceName] = $reflection;
							}
						}
					}
				}
			}
		}
		$availableResources = \array_keys($ret);
		foreach ($ret as $reflection) {
			/* @var $annotation Reflection\CacheResourceReflection */
			$reflection->description->validate($availableResources);
		}
		return $ret;
	}

	/**
	 * return all resources that are annotated as cacheable
	 *
	 * @return string[] resource names
	 */
	public function getAvailableResources()
	{
		$dir = $this->getDirectory();
		$ret = array_keys($dir);
		sort($ret);
		return $ret;
	}

	/**
	 *
	 * get direct dependencies for resource
	 *
	 * @param string $resourceName
	 *
	 * @return string[] contains dependent resource names
	 */
	public function getDependencies($resourceName)
	{
		$directory = $this->getDirectory();
		$dependencies = array();
		foreach ($directory as $availableResourceName => $reflection) {
			/* @var $reflection Reflection\CacheResourceReflection */
			if (in_array($resourceName, $reflection->description->dependencies)) {
				$dependencies[] = $availableResourceName;
			}
		}
		\sort($dependencies);
		return $dependencies;
	}
	/**
	 * builds a tree of dependent resource names
	 *
	 * @param string $resourceName
	 * @param int $level recursion level
	 *
	 * @return array
	 */
	private function walkDependencyTree($resourceName, $level)
	{
		$dependenciesAtLevel = array();
		foreach ($this->getDependencies($resourceName) as $dep) {
			$childDependencies = $this->walkDependencyTree($dep, $level++);
			$dependenciesAtLevel[$dep] = $childDependencies;
		}
		return $dependenciesAtLevel;
	}
	/**
	 * linearizes dependency tree
	 *
	 * @param \Foomo\Cache\CacheResource $resourceName
	 * @param array $dependencyList
	 * @param int $level recursion level
	 */
	private function walkDependencyList($resourceName, &$dependencyList, $level = 0)
	{
		foreach($this->getDependencies($resourceName) as $dep) {
			$dependencyList[] = $dep;
			$this->walkDependencyList($dep, $dependencyList, $level + 1);
		}
	}
	/**
	 * check if string contains cache annotation
	 *
	 * @param string $str
	 *
	 * @return bool
	 */
	private function containsCacheAnnotation($str)
	{
		return strpos($str, '@Foomo\Cache\CacheResourceDescription') !== false;
	}
	/**
	 * generate a string representation of the dependency tree
	 *
	 * @param string $resourceName
	 *
	 * @return string
	 */
	public function renderDependencyTree($resourceName)
	{
		$rendering = 'dependency tree for ' . $resourceName . ' :' . PHP_EOL;
		$this->crawlDependencyTree($resourceName, $rendering);
		return $rendering;
	}
	/**
	 * recursive walk through the dependency tree generating string representation
	 * of dependencies
	 */
	private function crawlDependencyTree($resourceName, &$rendering, $level = 0)
	{
		foreach ($this->getDependencies($resourceName) as $dep) {
			$rendering .= \str_repeat('  ', $level) . $dep . PHP_EOL;
			$this->crawlDependencyTree($dep, $rendering, $level + 1);
		}
	}
}
