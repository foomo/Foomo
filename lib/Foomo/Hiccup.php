<?php

/*
 * bestbytes-copyright-placeholder
 */

namespace Foomo;

use Foomo\Utils;

/**
 * the hiccup model, provides functionality to get a site back up and running, when things got stuck
 *
 * @todo command line mode
 * @todo set run mode
 * @todo clear ALL caches
 *
 * Includes for the SharedCache and avalanche facade
 */
class Hiccup {

	public static function getStatus()
	{
		$contents = file_get_contents(Utils::getServerUrl(null, true) . '/' . \Foomo\ROOT_HTTP . '/status.php');
		try {
			if ($contents) {
				return \Foomo\Yaml::parse($contents);
			}
		} catch (\Exception $e) {
			trigger_error('status was unreadable', E_USER_WARNING);
		}
	}

	public static function removeAutoloaderCache()
	{
		\Foomo\Cache\Manager::reset('Foomo\\AutoLoader::cachedGetClassMap', false);
		\Foomo\Cache\Manager::reset('Foomo\\Cache\\DependencyModel->cachedGetDirectory', false);
		\Foomo\Cache\Manager::reset('Foomo\\MVC\\AppDirectory::cachedGetAppClassDirectory', false);
		\Foomo\Cache\Manager::reset('Foomo\\Modules\\Manager::cachedGetEnabledModulesOrderedByDependency', false);
	}

	public static function removeConfigCache()
	{
		\Foomo\Cache\Manager::reset('Foomo\\Config::cachedGetConf', false);
	}

}
