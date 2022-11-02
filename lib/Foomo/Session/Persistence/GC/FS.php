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

use SplFileInfo;
use DirectoryIterator;
use Foomo\Session\Persistence\FS as FSPersistor;
use Foomo\Session;
use Foomo\Session\GC;

/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
class FS implements GCInterface {
	/**
	 * @var \DirectoryIterator
	 */
	private $dirIterator;
	private $valid;
	/**
	 * @var integer
	 */
	private $key;
	/**
	 *
	 * @var Item
	 */
	private $current;
	/**
	 * @var Session\GCPrinterInterface
	 */
	private $gcPrinter;
	public function __construct(\Foomo\Session\GCPrinterInterface $gcPrinter)
	{
		$this->gcPrinter = $gcPrinter;
		return;
	}

	#[\ReturnTypeWillChange]
	public function next()
	{
		$this->key ++;
	}
	public function valid(): bool
	{
		while (true) {
			$this->dirIterator->next();
			if($this->dirIterator->valid()) {
				$file = $this->dirIterator->current();
			} else {
				$this->valid = false;
				$this->current = null;
				return false;
			}
			/* @var $file DirectoryIterator */
			if ($file->isFile()) {
				$baseName = $file->getBasename();
				if (strpos($baseName, FSPersistor::PREFIX) === 0 && substr($baseName, -8) == FSPersistor::CONTENTS_POSTFIX) {
					// skipping contents file
					continue;
				}
				if (strpos($baseName, FSPersistor::PREFIX) === 0) { // && substr($baseName, -8) == 'contents') {
					$sessionId = substr($baseName, strlen(FSPersistor::PREFIX) + 1);
					$contentsFile = FSPersistor::getContentsFileName($sessionId); //;Session::foomoGetContentsFileName($sessionId);
					if (file_exists($contentsFile)) {
						$contentsFileExists = true;
						$file = new SplFileInfo($contentsFile);
					} else {
						$contentsFileExists = false;
					}
					$aTime = $file->getATime();
					$timeSinceLastAccess = time() - $aTime;

					$this->gcPrinter->out('sessionId: ' . $sessionId .' timeSinceLastAccess : ' . $timeSinceLastAccess . ', aTime : ' . date(GC::DATE_FORMAT, $file->getATime()) . ', cTime : ' . date(GC::DATE_FORMAT, $file->getCTime()));

					$this->current = new Item;
					//$this->key ++;
					$this->valid = true;
					$this->current->sessionId = $sessionId;
					$this->current->lastReadAccess = $file->getATime();
					$this->current->lastWriteAccess = $file->getCTime();
					return true;
				}
			}
		}

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
		$this->dirIterator = new DirectoryIterator(ini_get('session.save_path'));
		$this->valid = false;
		$this->key = 0;
	}
}