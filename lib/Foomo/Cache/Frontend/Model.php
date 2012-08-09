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

namespace Foomo\Cache\Frontend;

use Foomo\Cache\Persistence\Expr;
use Foomo\Modules\Manager;
use Foomo\AutoLoader;

/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
class Model {

	/**
	 * @var Foomo\Cache\DependencyModel
	 */
	private $dependencyModel;
	public $currentResourceName;
	public $currentResourceId;
	public $currentResource;
	public $currentInvalidationList;
	public $currentInvalidationPolicy;
	public $renderedDependencies;
	public $currentOperation;
	public $dependencyCurrentResourceName;
	public $resourcePropertiesCurrentResource;
	public $advancedInvalidationUserExpression;
	public $advancedInvalidationUserExpressionString;
	public $advancedInvalidationUserExpressionInterpretationString;
	public $advancedInvalidationUserExpressionError;
	public $addedResources;
	public $currentExpressionResultsNumber;

	public function __construct()
	{
		$this->dependencyModel = \Foomo\Cache\DependencyModel::getInstance();
		$this->currentInvalidationPolicy = \Foomo\Cache\Invalidator::POLICY_INSTANT_REBUILD;
	}

	public function getResourceList()
	{

		$availableResources = $this->dependencyModel->getAvailableResources();
		//return $availableResources;
		$modules = Manager::getEnabledModules();
		$ret = array();
		//var_dump($availableResources);
		foreach ($modules as $module) {
			$ret[$module] = array('resources' => array());
			$libFolders = Manager::getModuleLibFolders($module);
			//var_dump($libFolders);
			foreach ($availableResources as $resourceName) {
				// this is a hack to keep the test mock objects from polluting the toolbox
				$hideName = 'Foomo\\Cache\\MockObjects';
				if(substr($resourceName, 0, strlen($hideName)) == $hideName) {
					continue;
				}
				$resourceClassName = $this->resourceNameToClassName($resourceName);
				$resourceClassFile = AutoLoader::getClassFileName($resourceClassName);
				// var_dump($resourceClassFile);
				foreach ($libFolders as $libFolder) {

					if (strpos($resourceClassFile, $libFolder) === 0) {
						$ret[$module]['resources'][] = $resourceName;
						break;
					}
				}
			}
		}
		return $ret;
	}

	public function getDependencies($resourceName)
	{
		return $this->dependencyModel->getDependencies($resourceName);
	}

	public function getDependenciesList($resourceName)
	{
		return $this->dependencyModel->getDependenciesList($resourceName);
	}

	public function getDependencyTree($resourceName)
	{
		return $this->dependencyModel->getDependencyTree($resourceName);
	}

	public function validateUserExpression($expressionString)
	{
		$this->advancedInvalidationUserExpressionString = $expressionString;
		$this->evaluateAdvancedUserExpression();
	}

	public function getAvailableResources()
	{
		return $this->dependencyModel->getAvailableResources();
	}

	public function getToplevelResources() {
		$this->addedResources[] = array();
		$resources = $this->getAvailableResources();
		$ret = array();
		foreach ($resources as $resourceName) {
			if ($this->hasChildren($resourceName)) {
				$ret[] = $resourceName;
				$this->addedResources[] = $resourceName;
			}
		}
		sort($ret);
		//check if topped by somebody else
		$toRemove = array();
		foreach($ret as $resourceName) {
			$children = $this->dependencyModel->getDependencies($resourceName);
			foreach($children as $child) {
				if (in_array($child,$ret)) {
					$toRemove[] = $child;
				}
			}
		}
		//remove topped 
		foreach ($ret as $key => $value) {
			if (\in_array($value, $toRemove)) {
				unset($ret[$key]);
			}
		}
		return $ret;
	}
	

	private function evaluateAdvancedUserExpression()
	{
		$tempFile = \tempnam(\Foomo\Config::getTempDir(), 'avalancheExpr-');
		$errorFile = self::getErrorFile($tempFile);
		$exprStr = $this->advancedInvalidationUserExpressionString;
		\file_put_contents($tempFile, $this->advancedInvalidationUserExpressionString);
		$exprId = \basename($tempFile);
		$res = \file_get_contents(\Foomo\Utils::getServerUrl(null, true) . \Foomo\ROOT_HTTP . '/avalanche.php?exprId=' . \urlencode($exprId));
		if ($res == 'ok') {
			$this->advancedInvalidationUserExpression = \unserialize(\file_get_contents($tempFile));
			//$this->advancedInvalidationUserExpressionString = $this->advancedInvalidationUserExpression;
			$this->advancedInvalidationUserExpressionInterpretationString = $this->getExpressionInterpretation();
		} else {
			$this->advancedInvalidationUserExpression = null;
			$this->advancedInvalidationUserExpressionString = $exprStr;
			$this->advancedInvalidationUserExpressionInterpretationString = '';
			if (file_exists($errorFile)) {
				$this->advancedInvalidationUserExpressionError = \file_get_contents($errorFile);
			} elseif (!$this->advancedInvalidationUserExpression) {
				$this->advancedInvalidationUserExpressionError = 'sth really bad happened - you might want to check the log ...';
			}
			//$this->advancedInvalidationUserExpressionString = $this->advancedInvalidationUserExpression;
			$this->advancedInvalidationUserExpressionInterpretationString = $this->getExpressionInterpretation = '';
		}
	}

	private function getExpressionInterpretation()
	{
		if (!is_null($this->advancedInvalidationUserExpression)) {
			return \Foomo\Cache\Manager::getExpressionInterpretation($this->currentResourceName, $this->advancedInvalidationUserExpression);
		} else {
			return '';
		}
	}

	private function resourceNameToClassName($resourceName)
	{
		if (strpos($resourceName, '->') !== false) {
			$separator = '->';
		} else if (strpos($resourceName, '::') !== false) {
			$separator = '::';
		} else {
			return null;
		}
		$parts = explode($separator, $resourceName);
		return $parts[0];
	}

	public function hasChildren($resourceName)
	{
		$dependencies = $this->dependencyModel->getDependencies($resourceName);
		if (count($dependencies) > 0) {
			return true;
		} else {
			return false;
		}
	}

	public function getCachedResourcesList()
	{
		$resourceName = $this->currentResourceName;
		if ($resourceName == null)
			return array();
		$expr = Expr::idNe('This could never be an id');
		$resources = \Foomo\Cache\Manager::query($resourceName, $expr, 100, 0);
		return $resources;
	}

	public function setCurrentResourceForId($resourceName, $id)
	{
		$expr = Expr::idEq($id);
		$resources = \Foomo\Cache\Manager::query($resourceName, $expr, 100, 0);
		if (count($resources) == 1) {
			$this->currentResource = $resources->current();
		} else {
			$this->currentResource = null;
		}
	}

	public function getEmptyResource($resourceName)
	{

		return \Foomo\Cache\Proxy::getEmptyResourceFromResourceName($resourceName);
	}

	public function getOrphanTopLevelResources()
	{
		if (empty($this->addedResources))
			$this->getToplevelResources();

		$resources = $this->getAvailableResources();
		$ret = array();
		foreach ($resources as $resourceName) {
			if (!\in_array($resourceName, $this->addedResources)) {
				$ret[] = $resourceName;
				$this->addedResources[] = $resourceName;
			}
		}
		return $ret;
	}

	private static function getErrorFile($tempFile)
	{
		return $tempFile . '-error';
	}

	public static function evaluateExpr($exprId)
	{
		$userInputFile = realpath(\Foomo\Config::getTempDir() . \DIRECTORY_SEPARATOR . $exprId);
		$tempFile = \realpath(\Foomo\Config::getTempDir() . \DIRECTORY_SEPARATOR . $exprId);

		if ($userInputFile != $tempFile) {
			\trigger_error('smbdy is trying to do sth. very nasty with exprId: ' . $exprId . ' userInputFile : ' . $userInputFile . ' != tempFile : ' . $tempFile, E_USER_ERROR);
		}


		$errorFile = self::getErrorFile($tempFile);
		$userExpr = file_get_contents($tempFile);
		$ret = null;
		if (!empty($userExpr)) {
			eval('use Foomo\Cache\Persistence\Expr; $ret = ' . $userExpr . ';');
			if (is_null($ret)) {
				$lastError = \error_get_last();
				if ($lastError) {
					\file_put_contents($errorFile, $lastError['message']);
				} else {
					\file_put_contents($errorFile, '');
				}
			} else {
				if (!($ret instanceof Expr)) {
					$ret = null;
				}
			}
			\file_put_contents($tempFile, \serialize($ret));
		}
		echo!is_null($ret) ? 'ok' : 'nok';
		exit;
	}

	public function invalidateResources($resourceName, $expression, $invalidationPolicy)
	{
		$results = \Foomo\cache\Manager::query($resourceName, $expression);
		$invalidator = new \Foomo\Cache\Invalidator();
		echo 'invalidating with : ' . $this->advancedInvalidationUserExpressionString . PHP_EOL;
		if (count($results) == 0) {
			echo '  nothing matched ...' . PHP_EOL;
		} else {
			foreach ($results as $resource) {
				//force invalidation policy
				echo 'Invalidating dependency tree for ' . $resource->name . ' ' . $resource->id . \PHP_EOL;
				$resource->invalidationPolicy = $invalidationPolicy;
				$invalidator->invalidate($resource, true, true);
				echo '   Done.' . \PHP_EOL;
			}
		}
		echo 'Done.' . PHP_EOL;
	}

	public function prefectchResourcesToInvalidate($resourceName, $expression)
	{
		$results = \Foomo\cache\Manager::query($resourceName, $expression, 100, 0);
		$this->currentExpressionResultsNumber = count($results);
		$invalidator = new \Foomo\Cache\Invalidator();
		foreach ($results as $resource) {
			$this->currentInvalidationList = array_merge($this->currentInvalidationList, $invalidator->getInInvalidationList($resource, true));
		}
	}

	/**
	 * get the raw annotations
	 *
	 * @param string $resourceName
	 *
	 * @return array
	 */
	public function getRawAnnotationData($resourceName)
	{
		$resourceRefl = $this->getResourceRefl($resourceName);
		if ($resourceRefl && !empty($resourceRefl->sourceClassName)) {
			$methodRelf = new \ReflectionMethod($resourceRefl->sourceClassName, $resourceRefl->sourceMethodName);
			$parser = new \AnnotationsMatcher;
			$rawData = array();
			$parser->matches($methodRelf->getDocComment(), $rawData);
			$rawData = $rawData['Foomo\Cache\CacheResourceDescription'][0];
			$deps = array();
			if (!empty($rawData['dependencies'])) {
				foreach (explode(',', $rawData['dependencies']) as $dep) {
					$deps[] = trim($dep);
				}
			}
			$rawData['dependencies'] = $deps;
			return $rawData;
		} else {
			return array();
		}
	}

	public function getAnnotationValidationStatus($resourceName)
	{
		$directory = \Foomo\Cache\DependencyModel::getInstance()->getDirectory();
		$parameters = array();
		foreach ($directory as $availableResourceName => $reflection) {
			/* @var $reflection \Foomo\Cache\Reflection\CacheResourceReflection */
			if ($availableResourceName == $resourceName) {
				$reflection->description->validate();
				$status = $reflection->description->getAnnotationValidationStatus();
				if ($status != 'OK') {
					return $status;
				} else {
					return '';
				}
			}
		}
		return "Unknown resource: " . $resourceName;
	}

	/**
	 * @param string $resourceName
	 *
	 * @return Foomo\Cache\Reflection\CacheResourceReflection
	 */
	public function getResourceRefl($resourceName)
	{
		$delimiter = "::";
		if (\strpos($resourceName, "::")) {
			$delimiter = "::";
		} elseif (\strpos($resourceName, "->")) {
			$delimiter = "->";
		} else {
			\trigger_error(__CLASS__ . __METHOD__ . ": Invalid resource name.", \E_USER_WARNING);
			return null;
		}
		$pos = \strpos($resourceName, $delimiter);
		$class = \substr($resourceName, 0, $pos);
		$method = \substr($resourceName, $pos + 2);
		try {
			$refl = \Foomo\Cache\Reflection\CacheResourceReflection::getReflection($class, $method);
		} catch (\Exception $e) {
			trigger_error('can not reflect on ' . $class . '.' . $method, \E_USER_WARNING);
			$refl = null;
		}
		return $refl;
	}

	public function getStorageExists($resourceName)
	{
		return \Foomo\Cache\Manager::getQueryablePersistor()->storageStructureExists($resourceName);
	}

	public function getValidationStatus($resourceName)
	{
		return \Foomo\Cache\Manager::getQueryablePersistor()->validateStorageStructure($resourceName, false);
	}

}
