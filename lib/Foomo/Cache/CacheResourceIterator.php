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

namespace Foomo\Cache;

/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
class CacheResourceIterator implements \Iterator, \Countable {

	protected $keys;
	protected $count;
	protected $cursor = 0;

	public function current()
	{
		\trigger_error('implement me', E_USER_ERROR);
	}

	public function next()
	{
		$this->cursor++;
	}

	public function key()
	{
		return $this->keys[$this->cursor];
	}

	public function valid()
	{
		return $this->count > $this->cursor;
	}

	public function rewind()
	{
		$this->cursor = 0;
	}

	public function count()
	{
		return $this->count;
	}

}