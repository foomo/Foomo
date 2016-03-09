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
use Foomo\Modules\Resource\Fs;
use Foomo\Modules\Resource\NPMPackage;

/**
 * npmjs integration
 *
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
class NPM
{
	/**
	 * @return CliCall
	 */
	public static function getNPMCall($moduleName)
	{
		$args = func_get_args();
		$args = array_slice($args, 1);
		$call = CliCall::create(
			'npm',
			$args,
			[
				'HOME' => self::getNPMDir($moduleName),
			]
		);
		return $call;
	}

	public static function getNPMDir($moduleName)
	{
		return Config::getVarDir($moduleName) . DIRECTORY_SEPARATOR . 'npm';
	}

	public static function getNodeModulesDir($moduleName)
	{
		return self::getNPMDir($moduleName) . DIRECTORY_SEPARATOR . 'node_modules';
	}

	/**
	 * use this to get the absolute path to a program installed with NPM for your module
	 *
	 * @param string $moduleName
	 * @param string $command
	 *
	 * @return string
	 */
	public static function which($moduleName, $command)
	{
		return self::getNodeModulesDir($moduleName) . DIRECTORY_SEPARATOR . '.bin' . DIRECTORY_SEPARATOR . $command;
	}

	private static $packages = [];

	/**
	 * @param string $moduleName
	 * @param bool $forceReload
	 * @return Modules\Resource\NPMPackage[]
	 */
	public static function getInstalledPackages($moduleName, $forceReload = false)
	{
		if(!isset(self::$packages[$moduleName]) || $forceReload) {
			$npmDir = self::getNPMDir($moduleName);
			if(file_exists($npmDir)) {
				self::$packages[$moduleName] = [];
				$call = self::getNPMCall(
					$moduleName,
					'list',
					'-ll',
					'-json',
					'-depth',
					'0'
				);
				self::executeInNPMDir($moduleName, $call);
				if($call->exitStatus == 0) {
					self::$packages[$moduleName] = self::readPackages($call->stdOut);
				}
			}
		}
		return self::$packages;
	}

	private static function executeInNPMDir($moduleName, CliCall $call)
	{
		$pwd = getcwd();
		chdir(self::getNPMDir($moduleName));
		$call->execute();
		chdir($pwd);
	}

	/**
	 * @param $json
	 * @return array
	 * @internal
	 */
	public static function readPackages($json)
	{
		$packagesList = [];
		$packages = json_decode($json);
		if(!is_object($packages)) {
			return $packagesList;
		}
		foreach($packages->dependencies as $name => $dep) {
			$packagesList[] = NPMPackage::getResource($name, $dep->version, $dep->description);
		}
		return $packagesList;
	}

	public static function packageIsInstalled(Modules\Resource\NPMPackage $package)
	{
		foreach(self::getInstalledPackages($package->forModule) as $installedPackages) {
			foreach($installedPackages as $installedPackage) {
				if(
					$installedPackage->name == $package->name &&
					$installedPackage->version == $package->version
				) {
					return true;
				}
			}
		}
		return false;
	}
	public static function installPackage(Modules\Resource\NPMPackage $package)
	{
		// make sure workdir exists
		$npmDir = self::getNPMDir($package->forModule);
		$folderResource = Fs::getAbsoluteResource(Fs::TYPE_FOLDER, $npmDir);
		$folderResource->tryCreate();

		$call = self::getNPMCall(
			$package->forModule,
			'install',
			$package->name . '@' . $package->version
		);
		self::executeInNPMDir($package->forModule, $call);
		return $call->stdOut;
	}
}