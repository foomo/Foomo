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
 * framework setup helper
 * 
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 * @internal
 */
class Setup
{
	//---------------------------------------------------------------------------------------------
	// ~ Static variables
	//---------------------------------------------------------------------------------------------

	/**
	 * @var array
	 */
	static $configDirLayout = array(
		'common'	=> array(),
		'runMode'	=> array('modules')
	);
	/**
	 * @var array
	 */
	static $varDirLayout = array(
		'common'	=> array(),
		'runMode'	=> array(
			'basicAuth',
			'logs',
			'tmp',
			'cache',
			'htdocs',
			'modules',
			'htdocs/modules',
			'htdocs/modulesVar'
		)
	);

	//---------------------------------------------------------------------------------------------
	// ~ Public static methods
	//---------------------------------------------------------------------------------------------

	/**
	 * @return boolean
	 */
	public static function getVarAndConfigFoldersAreAvailable()
	{
		return \file_exists(\Foomo\CORE_CONFIG_DIR_CONFIG) && \is_writable(\Foomo\CORE_CONFIG_DIR_CONFIG) && \file_exists(\Foomo\CORE_CONFIG_DIR_VAR) && \is_writable(\Foomo\CORE_CONFIG_DIR_VAR);
	}

	/**
	 * was the default auth set up
	 *
	 * @return boolean
	 */
	public static function getDefaultAuthWasSetUp()
	{
		return file_exists(BasicAuth::getDefaultAuthFilename());
	}

	/**
	 * generate the cli inc
	 *
	 * @return boolean
	 */
	public static function generateShell()
	{
		$filename = self::getShellFilename();
		file_put_contents($filename, Module::getView(__CLASS__, 'shell')->render());
		\chmod($filename, 0777);
	}

	/**
	 * get the filename for the cli shell
	 *
	 * @return string
	 */
	public static function getShellFilename()
	{
		return
			\Foomo\CORE_CONFIG_DIR_MODULES . DIRECTORY_SEPARATOR .
			Module::NAME . DIRECTORY_SEPARATOR .
			'cli' . DIRECTORY_SEPARATOR .
			'php-' . Config::getMode();
	}

	/**
	 *
	 */
	public static function checkCoreConfigResources()
	{
		if (self::getVarAndConfigFoldersAreAvailable()) {
			if (defined('\Foomo\CORE_CONFIG_DIR_CONFIG')) {
				self::checkResourceFolder(\Foomo\CORE_CONFIG_DIR_CONFIG, '\Foomo\CORE_CONFIG_DIR_CONFIG');
				self::checkLayout(\Foomo\CORE_CONFIG_DIR_CONFIG, self::$configDirLayout);
			}
			if (defined('\Foomo\CORE_CONFIG_DIR_VAR')) {
				self::checkResourceFolder(\Foomo\CORE_CONFIG_DIR_VAR, '\Foomo\CORE_CONFIG_DIR_VAR');
				self::checkLayout(\Foomo\CORE_CONFIG_DIR_VAR, self::$varDirLayout);
			}
		}
	}

	//---------------------------------------------------------------------------------------------
	// ~ Private methods
	//---------------------------------------------------------------------------------------------

	/**
	 * @param string $rootFolder
	 * @param array $layout
	 */
	private static function checkLayout($rootFolder, $layout)
	{
		foreach ($layout['common'] as $commonDir) {
			self::createDir($rootFolder, $commonDir);
		}
		foreach (array('test', 'development', 'production') as $runMode) {
			self::createDir($rootFolder . DIRECTORY_SEPARATOR, $runMode);
			foreach ($layout['runMode'] as $runModeDir) {
				self::createDir($rootFolder . DIRECTORY_SEPARATOR . $runMode, $runModeDir);
			}
		}
	}

	/**
	 * create a folder in a pretty foolproof way
	 *
	 * @param string $rootFolder
	 * @param string $path
	 */
	private static function createDir($rootFolder, $path)
	{
		// exploding it with '/' is ok, because that is was is used cross platform in self::$..Layout
		$dirName = $rootFolder . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, explode('/', $path));
		if (!file_exists($dirName) && is_writable(dirname($dirName))) {
			//echo $dirName . PHP_EOL;
			mkdir($dirName);
		} else if (!file_exists($dirName) && !is_writable(dirname($dirName))) {
			$msg = 'could not create directory "' . $dirName . '"';
			echo $msg . PHP_EOL;
			trigger_error($msg, E_USER_ERROR);
		}
	}

	/**
	 *
	 * @param string $folder
	 * @param string $type
	 */
	private static function checkResourceFolder($folder, $type)
	{
		if (!is_dir($folder)) {
			$msg = 'invalid value for ' . $type . ' "' . $folder . '" does not exist';
			echo $msg . PHP_EOL;
			trigger_error($msg, E_USER_ERROR);
		}
	}
}