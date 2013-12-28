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
 * php request log entry
 *
 * the contents can be controlled through Foomo\Log\DomainConfig
 * 
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
class Entry {

	/**
	 * (quite) unique id for the log entry
	 * @var string
	 */
	public $id;
	/**
	 * timestamp very shortly after php process started
	 *
	 * @var float
	 */
	public $logTime;
	/**
	 * http status code - see http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html , extracted from apache log
	 *
	 * @var integer
	 */
	public $httpStatus;
	/**
	 * session session id
	 *
	 * @var string
	 */
	public $sessionId;
	/**
	 * age of the session
	 *
	 * @var integer
	 */
	public $sessionAge;
	/**
	 * recorded entries from $_SERVER
	 *
	 * @var array
	 */
	public $serverVars = array();
	/**
	 * phperrors, with traces if recorded
	 *
	 * @var array
	 */
	public $phpErrors = array();
	/**
	 * stop watch entries recorded through Foomo\Timer usage
	 *
	 * @var array
	 */
	public $stopwatchEntries = array();
	/**
	 * markers recorded through Foomo\Timer usage
	 *
	 * @var array
	 */
	public $markers = array();
	/**
	 * from $_POST
	 *
	 * @var array
	 */
	public $postVars = array();
	/**
	 * from $_GET
	 *
	 * @var array
	 */
	public $getVars = array();
	/**
	 * exception
	 *
	 * @var Exception
	 */
	public $exception;
	/**
	 * time in seconds from Foomo\SYSTEM_START_MICRO_TIME to Entry instantiation, can be overwritten from log value
	 *
	 * @var float
	 */
	public $runTime;
	public $processingTime = 0.1;
	/**
	 * peak memory usage in bytes
	 *
	 * @var integer
	 */
	public $peakMemoryUsage;
	/**
	 * name of the script file, that was processed
	 *
	 * @var string
	 */
	public $scriptFilename;
	/**
	 * outgoing bytes
	 *
	 * @var integer
	 */
	public $bytesOut;
	/**
	 * incoming bytes
	 *
	 * @var integer
	 */
	public $bytesIn;
	/**
	 * mvc path information
	 *
	 * @var array
	 */
	public $mvcPath;
	/**
	 * transactions, with status and runtime
	 *
	 * @var array
	 */
	public $transactions;
	/**
	 * typically basic auth user
	 *
	 * @var string
	 */
	public $remoteUser;

	const CONNECTION_STATUS_ABORTED = 'aborted';
	const CONNECTION_STATUS_CLOSED = 'closed';
	const CONNECTION_STATUS_KEEP_ALIVE = 'keepAlive';

	/**
	 * connection status, when response was completed from httpd log
	 * @var string
	 */
	public $connectionStatus;

	public function __construct()
	{
	}

	/**
	 * @param DomainConfig $settings
	 * @param array $phpErrors
	 * @param array $markers
	 * @param array $stopwatchEntries
	 * @param null $exception
	 * @param array $transactions
	 * @param int $processingTime
	 * @param array $mvcPath
	 * @return Entry
	 * @internal
	 */
	public static function create(DomainConfig $settings = null, array $phpErrors = array(), array $markers = array(), array $stopwatchEntries = array(), $exception = null, array $transactions = array(), $processingTime = 0, array $mvcPath = array())
	{
		$entry = new self();
		$entry->mvcPath = $mvcPath;
		$entry->processingTime = $processingTime;
		$entry->logTime = \Foomo\SYSTEM_START_MICRO_TIME;
		$entry->runTime = microtime(true) - $entry->logTime;
		$entry->sessionId = \Foomo\Session::getSessionIdIfEnabled();
		$entry->sessionAge = \Foomo\Session::getAge();
		$entry->id = \md5((isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'cli') . $entry->sessionId . \Foomo\SYSTEM_START_MICRO_TIME . \uniqid());
		$entry->phpErrors = $phpErrors;
		$entry->exception = $exception;
		foreach ($settings->trackServerVars as $varName) {
			if (isset($_SERVER[$varName])) {
				$entry->serverVars[$varName] = $_SERVER[$varName];
			}
		}
		$entry->markers = $markers;
		$entry->stopwatchEntries = $stopwatchEntries;
		$entry->peakMemoryUsage = memory_get_peak_usage();
		$entry->scriptFilename = $_SERVER['SCRIPT_FILENAME'];
		$entry->transactions = $transactions;
		if($settings) {
			if ($settings->logGetVars) {
				$entry->getVars = $_GET;
			}
			if ($settings->logPostVars) {
				$entry->postVars = $_POST;
			}
		}
		return $entry;
	}

	/**
	 * connection speed bytes / sec
	 *
	 * @return float
	 */
	public function getConnectionSpeed()
	{
		if($this->runTime > 0) {
			return ($this->bytesIn + $this->bytesOut) / $this->runTime;
		} else {
			return 0;
		}
	}

}