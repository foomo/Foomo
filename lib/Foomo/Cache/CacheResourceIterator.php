<?php

/*
 * bestbytes-copyright-placeholder
 */

namespace Foomo\Cache;

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