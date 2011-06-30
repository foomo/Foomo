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

use Foomo\Utils;

/**
 * the hiccup model, provides functionality to get a site back up and running, when things got stuck
 *
 * Includes for the SharedCache and avalanche facade
 *
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 * @todo command line mode
 * @todo set run mode
 * @todo clear ALL caches
 */
class Hiccup
{
	//---------------------------------------------------------------------------------------------
	// ~ Public static methods
	//---------------------------------------------------------------------------------------------

	/**
	 * @return string
	 */
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

	/**
	 *
	 */
	public static function removeAutoloaderCache()
	{
		\Foomo\Cache\Manager::reset('Foomo\\AutoLoader::cachedGetClassMap', false);
		\Foomo\Cache\Manager::reset('Foomo\\Cache\\DependencyModel->cachedGetDirectory', false);
		\Foomo\Cache\Manager::reset('Foomo\\MVC\\AppDirectory::cachedGetAppClassDirectory', false);
		\Foomo\Cache\Manager::reset('Foomo\\Modules\\Manager::cachedGetEnabledModulesOrderedByDependency', false);
	}

	/**
	 *
	 */
	public static function removeConfigCache()
	{
		\Foomo\Cache\Manager::reset('Foomo\\Config::cachedGetConf', false);
	}
}
