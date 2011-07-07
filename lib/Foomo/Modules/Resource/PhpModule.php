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

use Foomo\Modules\Resource;

/**
 * foomo php module requirement
 * 
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
class PhpModule extends Resource {
	/**
	 * @var string
	 */
	public $name;
	public static function getResource($name)
	{
		$ret = new self;
		$ret->name = $name;
		return $ret;
	}
	public function resourceValid()
	{
		return in_array($this->name, get_loaded_extensions());
	}

	public function resourceStatus()
	{
		return  'required php module ' . $this->name . ' was ' . ($this->resourceValid()?'loaded':'not loaded');
	}

	public function tryCreate()
	{
		return 'can not create a php modules';
	}
}