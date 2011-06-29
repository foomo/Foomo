<?php

/*
 * bestbytes-copyright-placeholder
 */

namespace Foomo;

/**
 * foomo core module
 */
class Module extends \Foomo\Modules\ModuleBase implements \Foomo\Frontend\ToolboxInterface
{
	//---------------------------------------------------------------------------------------------
	// ~ Constants
	//---------------------------------------------------------------------------------------------

	const NAME = 'Foomo';

	//---------------------------------------------------------------------------------------------
	// ~ Overriden static methods
	//---------------------------------------------------------------------------------------------

	/**
	 * @todo maybe introduce versions const VERSION Major.Minor.Patch
	 * @staticvar boolean $firstRun
	 */
	public static function initializeModule()
	{
		\Foomo\Utils::addIncludePaths(array(
			\Foomo\ROOT . \DIRECTORY_SEPARATOR . 'vendor' . \DIRECTORY_SEPARATOR . 'symfony'
		));
	}

	/**
	 * @return string
	 */
	public static function getDescription()
	{
		return 'manages modules and provides a lightweight web oriented framework';
	}

	/**
	 *
	 * @return array
	 */
	public static function getResources()
	{
		$ret = array(
			Modules\Resource\PearPackage::getResource('Mail'),
			Modules\Resource\PearPackage::getResource('Mail_Mime'),
			Modules\Resource\Config::getResource(self::NAME, Session\DomainConfig::NAME)
		);
		if (\Foomo\Config::getMode() == \Foomo\Config::MODE_TEST) {
			$ret[] = \Foomo\Modules\Resource\Config::getResource(self::NAME, \Foomo\Cache\Test\DomainConfig::NAME);
		}
		return $ret;
	}

	//---------------------------------------------------------------------------------------------
	// ~ Toolbox interface methods
	//---------------------------------------------------------------------------------------------

	/**
	 * @return array
	 */
	public static function getMenu()
	{
		return array(
			\Foomo\Frontend\ToolboxConfig\MenuEntry::create('Root.Configuration', 'Configuration', 'Foomo', 'Foomo\\Config\\Frontend'),
			\Foomo\Frontend\ToolboxConfig\MenuEntry::create('Root.Modules', 'Modules', 'Foomo', 'Foomo\\Modules\\Frontend'),
			\Foomo\Frontend\ToolboxConfig\MenuEntry::create('Root.Log', 'Log', 'Foomo', 'Foomo\\Log\\Frontend'),
			\Foomo\Frontend\ToolboxConfig\MenuEntry::create('Root.Auth', 'Auth', 'Foomo', 'Foomo\\BasicAuth\\Frontend'),
			\Foomo\Frontend\ToolboxConfig\MenuEntry::create('Root.Cache', 'Auth', 'Foomo', 'Foomo\\Cache\\Frontend')
		);
	}
}