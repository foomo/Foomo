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
use Foomo\Modules\Manager;

/**
 * composer integration
 *
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
class Composer
{
	private static $enabled = false;

	/**
	 * @return CliCall
	 */
	private static function callComposer()
	{
		$args = array_merge(
			array(
				Module::getBaseDir('bin') . DIRECTORY_SEPARATOR . 'composer.phar',
				'--working-dir=' . CORE_CONFIG_DIR_COMPOSER,
				'--no-interaction'
			),
			func_get_args()
		);
		$call = CliCall::create(
			'php',
			$args,
			array(
				'COMPOSER_HOME' => CORE_CONFIG_DIR_COMPOSER
			)
		);
		$call->execute();
		if($call->exitStatus != 0) {
			trigger_error('failed to call composer : ' . $call->report, E_USER_WARNING);
		}
		return $call;
	}
	private static function getBaseDir()
	{
		return CORE_CONFIG_DIR_COMPOSER;
	}
	private static function getAutoloaderFile()
	{
		return self::getBaseDir() . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
	}
	private static function getComposerConfigFile()
	{
		return self::getBaseDir() . DIRECTORY_SEPARATOR . 'composer.json';
	}

	public static function init()
	{
		if(CORE_CONFIG_DIR_COMPOSER !== false) {
			self::$enabled = true;
			$autoloaderFile = self::getAutoloaderFile();
			if(!file_exists($autoloaderFile)) {
				trigger_error('bootstrapping composer');
				$packages = self::getAllRequiredPackages();
				$configFile = self::getComposerConfigFile();
				if(count($packages) > 0) {
					$newConfig = self::generateComposerConfig($packages);
					if(!file_exists($configFile) || $newConfig != file_get_contents($configFile)) {
						file_put_contents($configFile, $newConfig);
					}
					self::callComposer('update', '--optimize-autoloader', '--prefer-dist', '--no-progress');
				} else {
					unlink($configFile);
					// mark "compilation"
					touch(self::getAutoloaderFile());
				}
			}
			include_once($autoloaderFile);
		}
	}

	/**
	 * @param Modules\Resource\ComposerPackage[] $packages
	 *
	 * @return string
	 */
	private static function generateComposerConfig(array $packages)
	{
		$config = array(
			'name' => 'local-foomo-generated/local-foomo-generated',
			'require' => $packages,
			'authors' => array(
				array(
					'name' => 'foomo',
					'email' => 'foomo@foomo.org'
				)
			)
		);
		if(defined('JSON_PRETTY_PRINT')) {
			return json_encode($config, JSON_PRETTY_PRINT);
		} else {
			return json_encode($config);
		}
	}
	public static function getAllRequiredPackages()
	{
		$ret = array();
		foreach(Modules\Manager::getAvailableModules() as $availableModuleName) {
			foreach(Modules\Manager::getModuleResources($availableModuleName) as $resource) {
				if($resource instanceof Modules\Resource\ComposerPackage) {
					$ret[$resource->name] = $resource->version;
				}
			}
		}
		return $ret;
	}
	public static function getEnabled()
	{
		return self::$enabled;
	}
	public static function update()
	{
		//self::callComposer();
	}

	/**
	 * @return Modules\Resource\ComposerPackage[]
	 */
	public static function getInstalledPackages()
	{
		static $packages;
		if(!isset($packages)) {
			$packages = array();
			$cmd = self::callComposer('show', '-i');
			if($cmd->exitStatus === 0) {
				$lines = explode(PHP_EOL, trim($cmd->stdOut));
				foreach($lines as $line) {
					// oh what a parser
					$nextLine = str_replace('  ', ' ', $line);
					// shrinking away whitespace in composer output
					while(strlen($line) != strlen($nextLine)) {
						$line = $nextLine;
						$nextLine = str_replace('  ', ' ', $line);
					}
					// getting the values from the output
					$parts = explode(' ', $line);
					$name = $parts[0];
					if(count($parts) > 1) {
						$version = $parts[1];
					} else {
						$version = '';
					}
					$packages[] = Modules\Resource\ComposerPackage::getResource($name, $version, substr($line, strlen($name . $version) + 2));
				}

			}
		}
		return $packages;
	}

	public static function packageIsInstalled(Modules\Resource\ComposerPackage $package)
	{
		foreach(self::getInstalledPackages() as $installedPackage) {
			// @todo version handling ?!
			if($installedPackage->name == $package->name) {
				return true;
			}
		}
		return false;
	}
	public static function requirePackage(Modules\Resource\ComposerPackage $package)
	{
		return self::callComposer('require', '--no-progress', '--prefer-dist', $package->name . ':' . $package->version)->stdOut;
	}
	public static function make($target, \Foomo\Modules\MakeResult $result)
	{
		if(self::getEnabled()) {
			switch($target) {
				case 'clean':
					$result->addEntry('removing composer autoloader file to trigger a reinstall on the next reload');
					unlink(self::getAutoloaderFile());
					break;
				default:
					$result->addEntry('nothing to make for ' . $target . ' in ' . __CLASS__);
			}
		}
	}
}