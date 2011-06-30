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

namespace Foomo\Modules\Resource;

/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
class Module extends \Foomo\Modules\Resource {
	/**
	 * @var string
	 */
	public $name;
	/**
	 * @var string
	 */
	public $version;
	/**
	 *
	 * @param string $name
	 * @param string $version
	 *
	 * @return Foomo\Modules\Resource\Module
	 */
	public static function getResource($name, $version)
	{
		$ret = new self;
		$ret->name = $name;
		$ret->version = $version;
		return $ret;
	}
	public function resourceValid()
	{
		$installedVersion = \Foomo\Modules\Manager::getModuleVersion($this->name);
		if($installedVersion) {
			// var_dump($this->name, version_compare($this->version, $installedVersion, '>='));
			if(version_compare($installedVersion, $this->version) === -1) {
				return false;
			} else {
				return true;
			}
		} else {
			return false;
		}
	}

	public function resourceStatus()
	{
		if(in_array($this->name, \Foomo\Modules\Manager::getAvailableModules())) {
			if($this->resourceValid()) {
				return 'Module ' . $this->name . ' is available in required version ' . $this->version;
			} else {
				return 'Module ' . $this->name . ' has to be version >= ' . $this->version .' but is only ' . \Foomo\Modules\Manager::getModuleVersion($this->name);
			}
		} else {
			return 'Module ' . $this->name . ' is not available';
		}
	}

	public function tryCreate()
	{
		return 'can not create a module or upgrade it ;)';
	}

}