<?php

namespace Foomo\Session\Persistence\GC;

use SplFileInfo;
use DirectoryIterator;
use Foomo\Session\Persistence\FS as FSPersistor;
use Foomo\Session;
use Foomo\Session\GC;

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
	
	public function next()
	{
		$this->key ++;
	}
	public function valid()
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
	public function key()
	{
		return $this->key;
	}
	public function current()
	{
		return $this->current;
	}
	public function rewind()
	{
		$this->dirIterator = new DirectoryIterator(ini_get('session.save_path'));
		$this->valid = false;
		$this->key = 0;
	}
}