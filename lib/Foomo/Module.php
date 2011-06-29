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
			'Root.Configuration' => array('name' => 'Configuration', 'module' => self::NAME, 'app' => 'Foomo\\Config\\Frontend', 'action' => 'config', 'target' => '_self'),
			'Root.Modules' => array('name' => 'Modules', 'module' => self::NAME, 'app' => 'Foomo\\Modules\\Frontend', 'action' => 'modules', 'target' => '_self'),
			'Root.Log' => array('name' => 'Log', 'module' => self::NAME, 'app' => 'Foomo\\Log\\Frontend', 'action' => 'log', 'target' => '_self'),
			'Root.Auth'	=> array('name' => 'Auth', 'module' => self::NAME, 'app' => 'Foomo\\BasicAuth\\Frontend', 'action' => 'basicAuth', 'target' => '_self'),
			'Root.Cache' => array('name' => 'Cache', 'module' => self::NAME, 'app' => 'Foomo\\Cache\\Frontend', 'action' => 'cache', 'target' => '_self')
		);
	}
}