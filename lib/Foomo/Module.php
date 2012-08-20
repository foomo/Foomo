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
		include_once('Mail/mime.php');
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
			Modules\Resource\Config::getResource(self::NAME, Session\DomainConfig::NAME),
			Modules\Resource\Config::getResource(self::NAME, Jobs\DomainConfig::NAME)
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
			\Foomo\Frontend\ToolboxConfig\MenuEntry::create('Root.Configuration', 'Configuration', self::NAME, 'Foomo.Config'),
			\Foomo\Frontend\ToolboxConfig\MenuEntry::create('Root.Modules', 'Modules', self::NAME, 'Foomo.Modules'),
			\Foomo\Frontend\ToolboxConfig\MenuEntry::create('Root.System', 'System', self::NAME, 'Foomo.Info'),
			\Foomo\Frontend\ToolboxConfig\MenuEntry::create('Root.System.Info', 'Info', self::NAME, 'Foomo.Info'),
			\Foomo\Frontend\ToolboxConfig\MenuEntry::create('Root.System.Info.Php', 'PHP', self::NAME, 'Foomo.Info', 'php'),
			\Foomo\Frontend\ToolboxConfig\MenuEntry::create('Root.System.Info.Apc', 'APC', self::NAME, 'Foomo.Info', 'apc', array(), '_blank'),
			\Foomo\Frontend\ToolboxConfig\MenuEntry::create('Root.System.Info.Memcache', 'Memcache', self::NAME, 'Foomo.Info', 'memcache', array(), '_blank'),
			\Foomo\Frontend\ToolboxConfig\MenuEntry::create('Root.System.Auth', 'Auth', self::NAME, 'Foomo.BasicAuth'),
			\Foomo\Frontend\ToolboxConfig\MenuEntry::create('Root.System.Log', 'Log', self::NAME, 'Foomo.Log'),
			\Foomo\Frontend\ToolboxConfig\MenuEntry::create('Root.Cache', 'Cache', self::NAME, 'Foomo.Cache'),
			\Foomo\Frontend\ToolboxConfig\MenuEntry::create('Root.Modules.Foomo', 'MVC Scaffolder', self::NAME, 'Foomo.MVC'),
			\Foomo\Frontend\ToolboxConfig\MenuEntry::create('Root.Modules.Foomo', 'Jobs', self::NAME, 'Foomo.Jobs')
		);
	}
}