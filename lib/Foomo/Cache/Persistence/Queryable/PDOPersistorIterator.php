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
class PDOPersistorIterator extends \Foomo\Cache\CacheResourceIterator {

	/**
	 * @var PDOStatement
	 */
	private $pdoStatement;
	/**
	 * db row
	 *
	 * @var array
	 */
	private $currentRow;
	/**
	 * cache resource
	 *
	 * @var \Foomo\Cache\CacheResource
	 */
	private $currentResource;
	/**
	 * me size
	 *
	 * @var integer
	 */
	protected $count;

	/**
	 * PDOPersistorIterator constructor.
	 *
	 * @param \PDOStatement $statement
	 * @param string $resourceName
	 * @param integer $count
	 */
	public function __construct($statement, $resourceName, $count)
	{
		$this->pdoStatement = $statement;
		$this->resourceName = $resourceName;
		if ($statement) {
			$this->count = $count;
			$this->currentRow = $this->pdoStatement->fetch(\PDO::FETCH_ASSOC, 0);
			if ($this->currentRow) {
				$this->currentResource = PDOPersistor::rowToCacheResource($this->currentRow, $this->resourceName);
			}
			$this->cursor = 0;
		} else {
			$this->count = 0;
			$this->currentResource = null;
			$this->currentRow = false;
			$this->cursor = 0;
		}
	}

	/**
	 * @return \Foomo\Cache\CacheResource|null
	 * @throws \Exception
	 */
	#[\ReturnTypeWillChange]
	public function current()
	{
		if ($this->pdoStatement && $this->count != 0) {
			return $this->currentResource;
		} else {
			throw new \Exception('Accessing the value of an EmptyIterator');
		}
	}

	/**
	 * @throws \Exception
	 */
	public function next(): void
	{
		if ($this->pdoStatement && $this->count != 0) {
			$this->currentRow = $this->pdoStatement->fetch(\PDO::FETCH_ASSOC);
			if ($this->currentRow === false) {
				$this->currentResource = null;
			} else {
				$this->currentResource = PDOPersistor::rowToCacheResource($this->currentRow, $this->resourceName);
			}
			$this->cursor++;
		}
	}

	/**
	 * @return int
	 * @throws \Exception
	 */
	#[\ReturnTypeWillChange]
	public function key()
	{
		if ($this->pdoStatement && $this->count != 0) {
			return $this->cursor;
		} else {
			throw new \Exception('Accessing the value of an EmptyIterator');
		}
	}

	/**
	 * @return bool
	 */
	public function valid(): bool
	{
		if (!$this->pdoStatement || $this->count == 0) {
			return false;
		} else {
			return $this->count > $this->cursor;
		}
	}

	/**
	 *
	 */
	public function rewind(): void
	{
		$this->cursor = 0;
	}

	/**
	 * @return integer
	 */
	public function count(): int
	{
		if (!$this->pdoStatement) {
			return 0;
		} else {
			return $this->count;
		}
	}
}