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

namespace Foomo\Modules;

/**
 *
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
class MakeResult
{
	/**
	 * @var string
	 */
	public $moduleName;
	/**
	 * @var MakeResult\Entry[]
	 */
	public $entries = array();
	public function addEntry($message, $level = MakeResult\Entry::LEVEL_NOTICE, $success = true)
	{
		$entry = new MakeResult\Entry();
		$entry->success = $success;
		$entry->message = $message;
		$entry->level = $level;
		$this->entries[] = $entry;
	}
	public function __construct($moduleName)
	{
		$this->moduleName = $moduleName;
	}

	/**
	 * @return bool
	 */
	public function wasSuccessful()
	{
		foreach($this->entries as $entry) {
			if(!$entry->success) {
				return false;
			}
		}
		return true;
	}
}