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

namespace Foomo\Session\Persistence\GC;

use Foomo\Session;
use Foomo\Session\GC;

/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author uwe <uwe.quitter@bestbytes.com>
 */
class SQLite implements GCInterface
{
	/**
	 * @var string[]
	 */
	private $rows;
	/**
	 * @var boolean
	 */
	private $valid;
	/**
	 * @var integer
	 */
	private $key;
	/**
	 * @var Item
	 */
	private $current;
	/**
	 * @var Session\GCPrinterInterface
	 */
	private $gcPrinter;

	public function __construct(Session\GCPrinterInterface $gcPrinter)
	{
		$this->gcPrinter = $gcPrinter;
		return;
	}

	public function next(): void
	{
		$this->key++;
	}

	public function valid(): bool
	{
		if ($this->key < 0) {
			$this->key = 0;
		}
		if ($this->key >= count($this->rows)) {
			$this->valid = false;
			$this->current = null;
			return false;
		}
		$row = $this->rows[$this->key];
		$sessionId = $row['sessionId'];
		$modified = $row['lastWrite'];
		$accessed = $row['lastRead'];
		if (empty($accessed)) {
			$accessed = $modified;
		}
		$timeSinceLastAccess = time() - $accessed;

		$this->gcPrinter->out(sprintf(
			"\tsessionId: %s, aTime: %s, mTime: %s, last access %ds before",
			$sessionId,
			date(GC::DATE_FORMAT, $accessed),
			date(GC::DATE_FORMAT, $modified),
			$timeSinceLastAccess
		));

		$this->current = new Item;
		$this->valid = true;
		$this->current->sessionId = $sessionId;
		$this->current->lastWriteAccess = $modified;
		$this->current->lastReadAccess = $accessed;
		return true;
	}

	#[\ReturnTypeWillChange]
	public function key()
	{
		return $this->key;
	}

	#[\ReturnTypeWillChange]
	public function current()
	{
		return $this->current;
	}

	public function rewind(): void
	{
		$this->rows = Session\Persistence\SQLite::getAllSessions();
		$this->valid = false;
		$this->key = -1;
		$this->current = null;
	}
}