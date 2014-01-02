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

namespace Foomo\Log;

/**
 * iterates over a log file and returns entries
 * 
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
class Reader implements \Iterator {

	private $fp;
	private $current;
	private $filterFunction;
	private $mapping;

	public function __construct($file)
	{
		$this->fp = fopen($file, 'r');
		$this->mapping = Logger::getMapping();
		$this->filterFunction = array($this, 'defaultFilter');
	}

	public function defaultFilter(Entry $entry)
	{
		return true;
	}

	public function setFilter($filterFunction)
	{
		$this->filterFunction = $filterFunction;
	}

	/**
	 * current entry
	 *
	 * @return \Foomo\Log\Entry
	 */
	public function current()
	{
		return $this->current;
	}

	public function next()
	{

	}

	public function key()
	{

	}

	public function valid()
	{
		if (!$this->fp) {
			return false;
		}
		$this->current = null;
		while (!feof($this->fp)) {
			$line = fgets($this->fp);
			if(substr($line, -1) === PHP_EOL) {
				$entry = self::extractLogEntryFromLine($line, $this->mapping);
				if (!is_null($entry) && call_user_func_array($this->filterFunction, array($entry))) {
					$this->current = $entry;
					return true;
				}
			}
		}
		return false;
	}

	public function goToOffset($offset)
	{
		fseek($this->fp, $offset);
	}

	public function rewind()
	{
		if ($this->fp) {
			\fseek($this->fp, 0);
		}
	}

	const HTTP_EMPTY = '-';
	const LOG_DELIMITER = ' ';
	const MICROSEC_FACTOR = 1000000;
	const DISABLED_ENTRY = 'disabled';
	const PENDING_ENTRY = 'pending';
	/**
	 * extract a log entry from a logfile line and populate with additional data
	 * from that line
	 *
	 * @param string $line
	 * @param array $mapping
	 *
	 * @return Entry
	 * @internal
	 */
	public static function extractLogEntryFromLine($line, array $mapping)
	{
		$line = trim($line);
		if (empty($line)) {
			// is there one
			return null;
		} else {
			// read the entry from the back
			$lastDelimiterPos = \strrpos($line, self::LOG_DELIMITER);
			/* @var $entry Entry */
			$rawEntry = substr($line, $lastDelimiterPos + 1);
			if($rawEntry == self::DISABLED_ENTRY || $rawEntry == self::HTTP_EMPTY) {
				return null;
			} else {
				// make it more light weight
				$line = substr($line, 0, $lastDelimiterPos);
				// ini_set('html_errors', 'Off');
				// var_dump($line, $lastDelimiterPos, $rawEntry);
				$entry = false;
				if ($rawEntry == self::PENDING_ENTRY) {
					$fatalError = true;
				} else {
					$fatalError = false;
					$rawEntry = \base64_decode($rawEntry);
					if(function_exists('gzinflate')) {
						try {
							$rawEntry = @gzinflate($rawEntry);
						} catch(\Exception $e) {
							$rawEntry = false;
						}
					}
					if(false !== $rawEntry) {
						$entry = \unserialize($rawEntry);
					}
				}
				// get the rest from the front
				// cut the entry from the line, and the log time to make things faster
				if($entry === false) {
					$entryWasEmpty = true;
					$entry = new Entry();
				} else {
					$entryWasEmpty = false;
				}
				foreach ($mapping as $mappingRule) {
					// move forward
					// echo $line . PHP_EOL;
					$logPropName = isset($mappingRule['logPropName']) ? $mappingRule['logPropName'] : $mappingRule['entryProp'];
					// strip the prop
					$line = substr($line, strlen($logPropName . ':' . self::LOG_DELIMITER));
					// f... apache request time stripping
					if($logPropName == 'requestTime') {
						$line = strtotime(substr($line, 1, 26)) . substr($line, 28);
						// echo $line . PHP_EOL;
					}
					$nextPos = strpos($line, self::LOG_DELIMITER);
					$parsedValue = substr($line, 0, $nextPos);


					$line = substr($line, $nextPos + 1);
					$propName = $mappingRule['entryProp'];
					if (!is_null($mappingRule['entryProp'])) {
						switch ($mappingRule['conf']) {
							case '%{FOOMO_SESSION_AGE}e':
							case '%{FOOMO_SESSION_ID}e':
								if(!$entryWasEmpty) {
									if ($parsedValue != $entry->$propName) {
										trigger_error('httpd log and session entry ot of sync with entry: "' . ($entry->$propName) . '" != httpd: "' . $parsedValue . '" for ' . $propName, E_USER_WARNING);
									}
									continue;
								}
								break;
							case '%X':
								// connections status
								switch ($parsedValue) {
									case 'X':
										$parsedValue = Entry::CONNECTION_STATUS_ABORTED;
										break;
									case '+':
										$parsedValue = Entry::CONNECTION_STATUS_KEEP_ALIVE;
										break;
									case '-':
										$parsedValue = Entry::CONNECTION_STATUS_CLOSED;
										break;
								}
								break;
							case '%f':
								if(!$entryWasEmpty) {
									continue;
								}
								break;
							case '%t':
								// $parsedValue =
								if($entryWasEmpty) {
									$parsedValue = (int) $parsedValue;
								} else {
									// ours is better
									continue;
								}
								break;
							case '%D':
								// runtime in microsecs
								$parsedValue = (float) $parsedValue / self::MICROSEC_FACTOR;
								break;
						}
						if ($parsedValue != self::HTTP_EMPTY) {
							// populate the entry with httpd log data
							$entry->$propName = $parsedValue;
							// echo 'adding ' . $propName . ': ' . $parsedValue . PHP_EOL;
						} else {
							// echo 'skip ' . $mappingRule['entryProp'] . ': ' . $parsedValue . PHP_EOL;
						}
					}
				}
				if($fatalError) {
					$entry->httpStatus = 500;
				}
				return $entry;
			}
		}
	}

}