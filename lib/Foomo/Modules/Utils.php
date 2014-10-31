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

namespace Foomo\Modules;

use Exception;
use Foomo\Module;
use Foomo\Config;

/**
 * utilities for foomo modules
 *
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 * @internal
 */
class Utils {
	const UMASK_FOLDER = 0775;
	const UMASK_FILE = 0664;

	/**
	 * create a module skeleton
	 *
	 * @param string $name a nice and simple name - must be camel case - first character lowercase
	 * @param string $description what does it do
	 * @param string[] $dependencies a list of modules the new one depends on
	 */
	public static function createModule($name, $description, $dependencies = array())
	{
		// $name = strtolower($name);
		$confirmedDeps = array();
		$availableModules = Manager::getAvailableModules();
		foreach ($dependencies as $dep) {
			if (in_array($dep, $availableModules)) {
				$confirmedDeps[] = $dep;
			}
		}
		$dependencies = $confirmedDeps;
		if (empty($name)) {
			throw new Exception('module name must not be empty');
		}
		$moduleBase = \Foomo\CORE_CONFIG_DIR_MODULES . DIRECTORY_SEPARATOR . $name;
		if (is_dir($moduleBase) || file_exists($moduleBase)) {
			throw new Exception('module exists');
		}
		if (!is_writable(\Foomo\CORE_CONFIG_DIR_MODULES)) {
			throw new Exception('must be able to write in ' . $moduleBase);
		}
		$dirs = array(
			'cli',
			'lib',
			'locale',
			'tests',
			'views',
			'htdocs',
			'htdocs' . DIRECTORY_SEPARATOR . 'assets',
			'htdocs' . DIRECTORY_SEPARATOR . 'services',
			'vendor'
		);
		$moduleLib = 'lib';
		$namespaceParts = explode('.', $name);
		foreach ($namespaceParts as $namespacePart) {
			$moduleLib .= DIRECTORY_SEPARATOR . $namespacePart;
			$dirs[] = $moduleLib;
		}
		mkdir($moduleBase, self::UMASK_FOLDER);
		foreach ($dirs as $dir) {
			mkdir($moduleBase . DIRECTORY_SEPARATOR . $dir, self::UMASK_FOLDER);
		}
		$moduleClassName = $moduleBase . DIRECTORY_SEPARATOR . $moduleLib . DIRECTORY_SEPARATOR . 'Module.php';
		// module class
		file_put_contents(
				$moduleClassName, \Foomo\Module::getView(
					__CLASS__,
					'moduleClass',
					array(
						'name' => $name,
						'namespace' => implode('\\', $namespaceParts),
						'description' => $description,
						'dependencies' => $dependencies
					)
				)->render()
		);
		chmod($moduleClassName, self::UMASK_FILE);
		Config::resetCache();
	}

}