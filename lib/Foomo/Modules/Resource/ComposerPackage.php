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
 * composer package
 * 
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
class ComposerPackage extends Resource {

	/**
	 * @var string
	 */
	public $name;
	/**
	 * @var string
	 */
	public $version;
	/**
	 * @var string
	 */
	public $description;
	public static function getResource($name, $version, $description = null)
	{
		$ret = new self;
		$ret->name = $name;
		$ret->version = $version;
		$ret->description = $description;
		return $ret;
	}
	public function resourceValid()
	{
		return \Foomo\Composer::packageIsInstalled($this);
	}

	public function resourceStatus()
	{
		return  'Composer package ' . $this->name . ':' . $this->version . ' ' . ($this->resourceValid()?'is installed':'missing');
	}

	public function tryCreate()
	{
		\Foomo\Composer::requirePackage($this);
	}

}