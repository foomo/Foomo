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
 * a config resource
 * 
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
class Config extends \Foomo\Modules\Resource {

	/**
	 * module
	 *
	 * @var string
	 */
	public $module;
	/**
	 * config name
	 *
	 * @var string
	 */
	public $name;
	/**
	 * domain
	 *
	 * @var string
	 */
	public $subDomain;

	public function __construct($module, $domain, $subDomain = '')
	{
		$this->module = $module;
		$this->name = $domain;
		$this->subDomain = $subDomain;
	}

	/**
	 * get a config resource
	 *
	 * @param string $module
	 * @param string $name
	 * @param string $subDomain
	 *
	 * @return Foomo\Modules\Resources\Fs
	 */
	public static function getResource($module, $name, $subDomain = '')
	{
		return new self($module, $name, $subDomain);
	}

	public function resourceValid()
	{
		if (\Foomo\Config::confExists($this->module, $this->name, $this->subDomain)) {
			return true;
		} else {
			return false;
		}
	}

	public function resourceStatus()
	{
		if ($this->resourceValid()) {
			return 'Configuration for domain ' . $this->name . ($this->subDomain != '' ? '/' . $this->subDomain : '') . ' is ok';
		} else {
			$ret = 'you need to create a config for the module ' . $this->module . ' in the domain ' . $this->name;
			if (!empty($this->subDomain)) {
				return $ret .= ' and subDomain ' . $this->subDomain;
			}
			return $ret;
		}
	}

	public function tryCreate()
	{
		if (\Foomo\Config::confExists($this->module, $this->name, $this->subDomain)) {
			return 'config exists';
		} else {
			return
				'created default config for ' .
				$this->module . ' - ' . $this->name .
				' - ' . var_export(
					\Foomo\Config::setConf(
						\Foomo\Config::getDefaultConfig($this->name),
						$this->module,
						$this->subDomain
					),
					true
				);
		}
	}

}