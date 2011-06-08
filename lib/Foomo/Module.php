<?php

/*
 * bestbytes-copyright-placeholder
 */

namespace Foomo;

use Foomo\Modules\ModuleBase;

/**
 * foomo core module
 */
class Module extends ModuleBase {
	const NAME = 'Foomo';
	//@todo maybe introduce versions const VERSION Major.Minor.Patch
	public static function initializeModule()
	{
		static $firstRun = true;
		if ($firstRun) {
			// first initialization
			$firstRun = false;
		} else {
			// when reinitializing
		}
		\Foomo\Utils::addIncludePaths(array(
			//\Foomo\CORE_CONFIG_DIR_MODULES . \DIRECTORY_SEPARATOR . self::NAME . \DIRECTORY_SEPARATOR . 'vendor' . \DIRECTORY_SEPARATOR . 'symfony'
			\Foomo\ROOT . \DIRECTORY_SEPARATOR . 'vendor' . \DIRECTORY_SEPARATOR . 'symfony'
		));
	}

	public static function getDescription()
	{
		return 'manages modules and provides a lightweight web oriented framework';
	}

	public static function getResources()
	{
		$ret = array(
			Modules\Resource\PearPackage::getResource('Mail'),
			Modules\Resource\PearPackage::getResource('Mail_Mime'),
		);
		if (\Foomo\Config::getMode() == \Foomo\Config::MODE_TEST) {
			$ret[] = \Foomo\Modules\Resource\Config::getResource(self::NAME, \Foomo\Core\DomainConfig::NAME);
		}
		return $ret;
	}

}