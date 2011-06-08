<?php

/*
 * bestbytes-copyright-placeholder
 */

namespace Foomo\Cache;

/**
 * discovers dependencied among resources as defined by CacheResourceDescription method annotations
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
	 * @return Foomo\Cache\DependencyModel
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
	 * @param $invalidateDependencyModelCache invalidate the cache before returning, false default.
	 *
	 * @return array array('resourceName' => CacheResourceReflection)
	 */
	public function getDirectory($invalidateDependencyModelCache = false)
	{
		static $ret;
		if ($invalidateDependencyModelCache === true) {
			$ret = null;
			$emptyResource = Proxy::getEmptyResource($this, 'cachedGetDirectory', array());
			Manager::invalidate($emptyResource, true, Invalidator::POLICY_INSTANT_REBUILD);
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
		//die('au');
		$ret = array();
		$classMap = \Foomo\AutoLoader::getClassMap();
		foreach ($classMap as $className => $file) {
			if ($this->containsCacheAnnotation(\file_get_contents($file))) {
				$classRefl = new \ReflectionClass($className);
				if (!$classRefl->isAbstract() && !$classRefl->isInterface()) {
					foreach ($classRefl->getMethods() as $methodRefl) {
						/* @var $methodRefl \ReflectionMethod */
						if ($this->containsCacheAnnotation($methodRefl->getDocComment())) {
							$refl = Reflection\CacheResourceReflection::internalGetReflection($classRefl->getName(), $methodRefl->getName());
							if ($refl) {
								$ret[$refl->resourceName] = $refl;
							}
						}
					}
				}
			}
		}
		$availableResources = \array_keys($ret);
		foreach ($ret as $resourceName => $refl) {
			/* @var $annotation Reflection\CacheResourceReflection */
			$refl->description->validate($availableResources);
		}
		return $ret;
	}

	/**
	 * return all resources that are annotated as ceable
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
		$deps = array();
		foreach ($directory as $availableResourceName => $reflection) {
			/* @var $reflection Reflection\CacheResourceReflection */
			if (in_array($resourceName, $reflection->description->dependencies)) {
				$deps[] = $availableResourceName;
			}
		}
		\sort($deps);
		return $deps;
	}

	/**
	 * get the Foomo\Cache\CacheResourceDescription annotation for a resource
	 *
	 * @return Foomo\Cache\CacheResourceDescription
	 */
	private function getAnnotation($resourceName)
	{
		$directory = $this->getDirectory();
		foreach ($directory as $availableResourceName => $reflection) {
			/* @var $reflection Reflection\CacheResourceReflection */
			if ($availableResourceName == $resourceName) {
				return $reflection->description;
			}
		}
		return null;
	}

	/**
	 * builds a tree of dependent resource names
	 *
	 * @param CacheResource $resourceName
	 * @param array $dependencyList array('level1Res', array('leaf1', 'leaf2', array('leaf one level still below')))
	 *
	 * @param int $level recursion level
	 */
	private function walkDependencyTree($resourceName, $level)
	{
		$dependeCiesAtLevel = array();
		foreach ($this->getDependencies($resourceName) as $dep) {
			$childDependencies = $this->walkDependencyTree($dep, $level++);
			$annotation = $this->getAnnotation($dep);
			$dependeCiesAtLevel[$dep] = $childDependencies;
		}
		return $dependeCiesAtLevel;
	}

	/**
	 * linearizes dependency tree
	 *
	 * @param Foomo\Cache\CacheResource $resourceName
	 * @param array $dependencyList
	 * @param int $level recursion level
	 */
	private function walkDependencyList($resourceName, &$dependencyList, $level = 0)
	{
		foreach ($this->getDependencies($resourceName) as $dep) {
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
