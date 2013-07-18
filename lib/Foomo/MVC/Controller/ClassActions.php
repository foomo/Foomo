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

namespace Foomo\MVC\Controller;

/**
 * class actions
 *
 * @internal
 * 
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
use Foomo\Config;

class ClassActions {
    /**
     * @var string
     */
    public $files = array();
    /**
     * @var Action[]
     */
    public $actions = array();
	/**
	 * @var string
	 */
	public $controllerDir;

	/**
	 * @return bool
	 */
	public function isValid()
	{
		if(Config::getMode() == Config::MODE_PRODUCTION) {
			return true;
		} else {
			$knownFiles = array();
			foreach($this->files as $file => $mTime) {
				if(!file_exists($file) || filemtime($file) != $mTime) {
					return false;
				} else {
					$knownFiles[] = $file;
				}
			}
			$countFoundActions = 0;
			if(is_dir($this->controllerDir)) {
				$dirIterator = new \DirectoryIterator($this->controllerDir);
				foreach($dirIterator as $fileInfo) {
					/* @var $fileInfo \SplFileInfo */
					if($fileInfo->isFile() && substr($fileInfo->getFilename(),0 , -4) == '.php' && substr($fileInfo->getFilename(), 0, 6) == 'Action') {
						if(!in_array($knownFiles, $fileInfo->getPathname())) {
							return false;
						} else {
							$countFoundActions ++;
						}
					}
				}
			}
			return $countFoundActions == count($this->files) - 1;
		}
	}
	public function addFile($filename)
	{
		$this->files[$filename] = filemtime($filename);
	}
}
