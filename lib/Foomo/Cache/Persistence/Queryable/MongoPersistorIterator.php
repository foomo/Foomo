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

namespace Foomo\Cache\Persistence\Queryable;

/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
class MongoPersistorIterator extends \Foomo\Cache\CacheResourceIterator
{

	private $mongoCursor;

	public function __construct($cursor)
	{
		$this->mongoCursor = $cursor;

		if ($this->mongoCursor->count() > 0) {
			$this->mongoCursor->next();

		}
	}

	/**
	 * @return \Foomo\Cache\CacheResource
	 */
	#[\ReturnTypeWillChange]
	public function current()
	{
		$document = $this->mongoCursor->current();
		if ($document) {
			return MongoPersistor::mapDocumentToResource($document);
		} else {
			return $document;
		}
	}

	#[\ReturnTypeWillChange]
	public function next()
	{
		return $this->mongoCursor->next();
	}

	#[\ReturnTypeWillChange]
	public function key()
	{
		return $this->mongoCursor->key;
	}

	public function valid(): bool
	{
		return $this->mongoCursor->valid();
	}

	public function rewind(): void
	{
		$this->mongoCursor->rewind();
	}

	public function count(): int
	{
		return $this->mongoCursor->count();
	}
}