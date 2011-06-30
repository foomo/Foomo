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

namespace Foomo\Log\Plot;

use \Foomo\Log\Entry;

/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
class EntryPlotter {

	/**
	 * color of the entry
	 *
	 * @var int
	 */
	public $color = 0x000000;
	/**
	 * entry to plot
	 *
	 * @var Entry
	 */
	public $entry;
	public $label = '';
	public function __construct(Entry $entry)
	{
		$this->entry = $entry;
	}

	public function plot()
	{
		$data = \Foomo\Module::getView('Foomo\\Log' , 'entry', $this)->render();
		return $data;
	}

	public function getErrorColor()
	{
		if ($this->entry->exception) {
			return 0xff0000;
		}
		return 0x00ff00;
		foreach ($this->entry->phpErrors as $phpError) {
			$phpError['no'];
		}
	}

}