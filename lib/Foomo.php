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

/**
 * manages the application runmode and configuration
 *
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
class Foomo
{
	private static $buildNumber;
	public static function getBuildNumber()
	{
		if(!isset(self::$buildNumber)) {
			$buildNumberFile = self::getBuildNumberFile();
			if(!file_exists($buildNumberFile)) {
				self::incrementBuildNumber();
			}
			self::$buildNumber = (int) trim(file_get_contents($buildNumberFile));
		}
		return self::$buildNumber;
	}
	public static function incrementBuildNumber()
	{
		Foomo\Lock::lock($lockName = 'foomo-buildNumber', true);
		$buildNumberFile = self::getBuildNumberFile();
		$buildNumber = -1;
		if(file_exists($buildNumberFile)) {
			$buildNumber = self::getBuildNumber();
		}
		$buildNumber ++;
		file_put_contents($buildNumberFile, (string) ($buildNumber));
		self::$buildNumber = $buildNumber;
		Foomo\Lock::release($lockName);
		\Foomo\Modules\Manager::updateSymlinksForHtdocs();
	}

	private static function getBuildNumberFile()
	{
		return Foomo\Config::getVarDir(Foomo\Module::NAME) . DIRECTORY_SEPARATOR . 'buildNumber';
	}
}
