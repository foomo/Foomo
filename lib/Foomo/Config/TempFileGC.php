<?php

/*
 * This file is part of the foomo Opensource Framework.
 * 
 * The foomo Opensource Framework is free software: you can redistribute it
 * and/or modify it under the terms of the GNU Lesser General Public License as
 * published  by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 * 
 * The foomo Opensource Framework is distributed in the hope that it will
 * be useful, but WITHOUT ANY WARRANTY; without even the implied warranty
 * of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public License along with
 * the foomo Opensource Framework. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Foomo\Config;
 
/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author Jan Halfar jan@bestbytes.com
 */
class TempFileGCCreator
{
	public static function getGC()
	{
		// loop all enabled modules
		/* @var $gc \Foomo\Jobs\Common\FileGC */
		$gc = \Foomo\Jobs\Common\FileGC::create()
			->recursive(true)
			->maxExecutionTime(4000)
			->addDirectories(array(\Foomo\Config::getTempDir()))
		;
		foreach(\Foomo\Modules\Manager::getEnabledModules() as $module) {
			// every file has a toplevel temp dir - do not run recursively in here
			$moduleTempDir = realpath(\Foomo\Config::getTempDir($module));
			$gc->addProtectedDirectories(array($moduleTempDir));
			// scan for module temp dir resources
			$resources = \Foomo\Modules\Manager::getModuleResources($module);
			$moduleDirectories = array();
			foreach($resources as $resource) {
				if($resource instanceof \Foomo\Modules\Resource\Fs) {
					/* @var $resource \Foomo\Modules\Resource\Fs */
					$resourceFile = realpath($resource->getFileName());
					if(is_dir($resourceFile) && strpos($resourceFile, $moduleTempDir) === 0) {
						$moduleDirectories[] = $resourceFile;
					}
				}
			}
			// are there conflicts within thetemp dirs
			$nonConflictingDirs = array();
			$moduleDirectories = array_unique($moduleDirectories);
			foreach($moduleDirectories as $possiblyConfictingDir) {
				if(!self::dirConflicts($possiblyConfictingDir, $moduleDirectories)) {
					$nonConflictingDirs[] = $possiblyConfictingDir;
				}
			}
			if(!empty($nonConflictingDirs)) {
				$gc->addProtectedDirectories($nonConflictingDirs);
			}
		}
		return $gc;
	}
	/**
	 * is one of $dirs a subdirectory of $dir and does thus conflict?
	 * 
	 * @param string $dir
	 * @param string[] $dirs
	 * 
	 * @return boolean
	 */
	private static function dirConflicts($dir, $dirs)
	{
		foreach($dirs as $possiblyConflictingDir) {
			if(strlen($possiblyConflictingDir) > strlen($dir) && substr($possiblyConflictingDir, $dir) === 0) {
				return true;
			}
		} 
		return false;
	}
}