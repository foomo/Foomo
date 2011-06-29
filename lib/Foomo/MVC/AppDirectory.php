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

namespace Foomo\MVC;

use Foomo\AutoLoader;
use ReflectionClass;
use Foomo\Cache\Proxy;

/**
 * helps to resolve apps when only the app id is known
 */
class AppDirectory {

	/**
	 * resolve an app by its id
	 *
	 * @param string $appId app id
	 * 
	 * @return string name of the corresponding app class name
	 */
	public static function resolveClass($appId)
	{
		$directory = self::getAppClassDirectory();
		if (isset($directory[$appId])) {
			return $directory[$appId];
		} else {
			return false;
		}
	}

	/**
	 * @Foomo\Cache\CacheResourceDescription('dependencies'='Foomo\AutoLoader::cachedGetClassMap')
	 *
	 * @return array a hash with id => class name mapping
	 *
	 */
	public static function cachedGetAppClassDirectory()
	{
		$classMap = AutoLoader::getClassMap();
		$ret = array();
		foreach ($classMap as $className => $classFileName) {
			try {
				$refl = new ReflectionClass($className);
				if (!$refl->isAbstract() && $refl->isSubclassOf('Foomo\\MVC\\AbstractApp')) {
					$appId = $refl->getConstant('NAME');
					if (!$appId) {
						$appId = str_replace('\\', '.', $refl->getName());
					}
					$ret[$appId] = $refl->getName();
				}
			} catch (Exception $e) {
				
			}
		}
		return $ret;
	}

	public static function getAppClassDirectory()
	{
		return Proxy::call(__CLASS__, 'cachedGetAppClassDirectory');
	}

}