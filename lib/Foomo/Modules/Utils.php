<?php

/*
 * bestbytes-copyright-placeholder
 */

namespace Foomo\Modules;

use Exception;
use Foomo\Module;
use Foomo\Config;

/**
 * utilities for foomo modules
 * 
 * @internal
 */
class Utils {
	const UMASK_FOLDER = 0775;
	const UMASK_FILE = 0764;

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
			'htdocs' . DIRECTORY_SEPARATOR . 'js',
			'htdocs' . DIRECTORY_SEPARATOR . 'css',
			'htdocs' . DIRECTORY_SEPARATOR . 'img',
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