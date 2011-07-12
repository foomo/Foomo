<?php

/*
 * bestbytes-copyright-placeholder
 */

namespace Foomo;

/**
 * foomo core module
 *
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
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
		if (\Foomo\Config::getMode() == \Foomo\Config::MODE_TEST && in_array('Foomo.TestRunner', Modules\Manager::getEnabledModules())) {
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
			\Foomo\Frontend\ToolboxConfig\MenuEntry::create('Root.Configuration', 'Configuration', 'Foomo', 'Foomo.Config'),
			\Foomo\Frontend\ToolboxConfig\MenuEntry::create('Root.Modules', 'Modules', 'Foomo', 'Foomo.Modules'),
			\Foomo\Frontend\ToolboxConfig\MenuEntry::create('Root.System', 'System', 'Foomo', 'Foomo.Info'),
			\Foomo\Frontend\ToolboxConfig\MenuEntry::create('Root.System.Info', 'Info', 'Foomo', 'Foomo.Info'),
			\Foomo\Frontend\ToolboxConfig\MenuEntry::create('Root.System.Info.Php', 'PHP', 'Foomo', 'Foomo.Info', 'php'),
			\Foomo\Frontend\ToolboxConfig\MenuEntry::create('Root.System.Info.Apc', 'APC', 'Foomo', 'Foomo.Info', 'apc', array(), '_blank'),
			\Foomo\Frontend\ToolboxConfig\MenuEntry::create('Root.System.Info.Memcache', 'Memcache', 'Foomo', 'Foomo.Info', 'memcache', array(), '_blank'),
			\Foomo\Frontend\ToolboxConfig\MenuEntry::create('Root.System.Auth', 'Auth', 'Foomo', 'Foomo.BasicAuth'),
			\Foomo\Frontend\ToolboxConfig\MenuEntry::create('Root.System.Log', 'Log', 'Foomo', 'Foomo.Log'),
			\Foomo\Frontend\ToolboxConfig\MenuEntry::create('Root.Cache', 'Cache', 'Foomo', 'Foomo.Cache')
		);
	}
}