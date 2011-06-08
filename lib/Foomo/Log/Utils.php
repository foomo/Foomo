<?php

/*
 * bestbytes-copyright-placeholder
 */

namespace Foomo\Log;

class Utils {

	/**
	 *
	 * @var Reader
	 */
	public $reader;
	private $printer;

	public function __construct()
	{
		$this->printer = new Printer();
	}

	public function setFile($file)
	{
		$this->reader = new Reader($file);
	}

	/**
	 * print all log entries for a day
	 * 
	 * @param string $day Y-m-d formatted day
	 */
	public function printEntries()
	{
		$i = 0;
		foreach ($this->reader as $entry) {
			$i++;
			echo $this->printer->printSeparator($i);
			echo $this->printer->printEntry($entry);
		}
	}

	public function printSessions()
	{
		$i = 0;
		foreach ($this->getSessions() as $session) {
			$i++;
			echo $this->printer->printSeparator($i);
			echo $this->printer->printSession($session);
		}
	}

	public function getSessions()
	{
		$ret = array();
		/* @var $entry Entry */
		foreach ($this->reader as $entry) {
			if ($entry->sessionId) {
				if (!isset($ret[$entry->sessionId])) {
					$ret[$entry->sessionId] = new UserSession($entry->sessionId);
				}
				$ret[$entry->sessionId]->addEntry($entry);
			}
		}
		return $ret;
	}

	public function webTail($filename, $filterFunction = null)
	{
		ini_set('max_execution_time', 0);
		if (!headers_sent()) {
			header('Content-Type: text/plain');
		}
		echo 'starting to tail ' . $filename . PHP_EOL;
		$this->flush();
		\Foomo\Session::saveAndRelease();
		$i = 0;
		$start = time();
		if (\file_exists($filename)) {
			$lastSize = \filesize($filename);
			while (!connection_aborted()) {
				//echo 'check ' . $lastSize . PHP_EOL;
				clearstatcache();
				$newSize = filesize($filename);
				if ($newSize != $lastSize) {
					$this->setFile($filename);
					if ($filterFunction) {
						$this->reader->setFilter($filterFunction);
					}
					$this->reader->goToOffset($lastSize);
					while ($this->reader->valid()) {
						$entry = $this->reader->current();
						$this->reader->next();
						$i++;
						echo '--------------------------------- ' . $i . ' ---------------------------------------' . PHP_EOL;
						echo $this->printer->printEntry($entry);
					}
				} else {
					if (time() % 10 == 0) {
						echo date('H:i:s') . PHP_EOL;
					}
				}
				$lastSize = $newSize;
				$this->flush();
				sleep(1);
			}
		} else {
			echo 'given log file does not exist';
		}
	}

	private function flush()
	{
		@ob_flush();
		flush();
	}

}