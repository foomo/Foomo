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
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
class UserSession {

	public $sessionId;
	public $errors = array(
		'notice' => 0,
		'warning' => 0,
		'error' => 0,
		'exception' => 0,
		'other' => 0
	);
	public $calls = array();
	private $speedData = array();

	public function __construct($sessionId)
	{
		$this->sessionId = $sessionId;
	}

	const BYTES_OUT_THRESHOLD = 65536;

	public function addEntry(Entry $entry)
	{
		foreach ($entry->phpErrors as $error) {
			switch ($error['no']) {
				case \E_NOTICE:
				case \E_USER_NOTICE:
					$part = 'notice';
					break;
				case \E_ERROR:
				case \E_CORE_ERROR:
				case \E_USER_ERROR:
					$part = 'error';
					break;
				case \E_WARNING:
				case \E_CORE_WARNING:
				case \E_USER_WARNING:
					$part = 'warning';
					break;
				default:
					$part = 'other';
			}
			$this->errors[$part]++;
		}
		if (isset($entry->scriptFilename)) {
			if (!isset($this->calls[$entry->scriptFilename])) {
				$this->calls[$entry->scriptFilename] = 0;
			}
			$this->calls[$entry->scriptFilename]++;
		}
		if (!is_null($entry->exception)) {
			$this->errors['exception']++;
		}

		if ($entry->bytesOut > self::BYTES_OUT_THRESHOLD) {
			$this->speedData[] = array($entry->bytesOut, $entry->runTime);
		}
	}

	public function getSpeedEstimate()
	{
		if (count($this->speedData) == 0) {
			return 'there were no calls with more than ' . self::BYTES_OUT_THRESHOLD . ' bytes downloaded';
		} else {
			$totalBytes = 0;
			$totalTime = 0;
			foreach ($this->speedData as $speedData) {
				$totalBytes += $speedData[0];
				$totalTime += $speedData[1];
			}
			return ($totalBytes / $totalTime) . ' bytes / sec from ' . count($this->speedData) . ' measurable calls';
		}
	}

}