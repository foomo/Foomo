<?php

/*
 * bestbytes-copyright-placeholder
 */

namespace Foomo\Log\Frontend;

use Foomo\Modules\Manager;
use Foomo\AutoLoader;

class Model {

	public function getFiltersProviders()
	{
		$classmap = AutoLoader::getClassMap();
		$classes = \array_keys($classmap);
		$providers = array();
		foreach ($classes as $className) {
			if (\class_exists($className)) {
				$refl = new \ReflectionClass($className);
				if ($refl->isSubclassOf('Foomo\Log\Filters\AbstractFilterProvider')) {
					$module = Manager::getClassModule($className);
					if (!isset($providers[$module])) {
						$providers[$module] = array();
					}
					$providers[$module][$refl->getName()] = array();
					foreach ($refl->getMethods() as $methodRefl) {
						/* @var $methodRefl \ReflectionMethod */
						$providers[$module][$refl->getName()][$methodRefl->getName()] = $methodRefl->getDocComment();
					}
				}
			}
		}
		return $providers;
	}

	public function webTail($filters)
	{
		header('Content-Type: text/plain;charset=utf-8;');

		echo __METHOD__ . ':' . PHP_EOL . PHP_EOL . implode(PHP_EOL, $filters) . PHP_EOL . PHP_EOL;

		$filter = function (\Foomo\Log\Entry $entry) use ($filters) {
					foreach ($filters as $filter) {
						$filter = explode('::', $filter);
						if (!\call_user_func_array($filter, array($entry))) {
							return false;
						}
					}
					return true;
				};

		$utils = new \Foomo\Log\Utils();
		$utils->webTail(\Foomo\Log\Logger::getLoggerFile(), $filter);
	}

}