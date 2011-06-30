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

	public function __construct(DomainConfig $settings, array $phpErrors, array $markers, array $stopwatchEntries, $exception, array $transactions, $processingTime)
	{
		$this->processingTime = $processingTime;
		$this->logTime = \Foomo\SYSTEM_START_MICRO_TIME;
		$this->runTime = microtime(true) - $this->logTime;
		$this->sessionId = \Foomo\Session::getSessionIdIfEnabled();
		$this->sessionAge = \Foomo\Session::getAge();
		$this->id = \md5((isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'cli') . $this->sessionId . \Foomo\SYSTEM_START_MICRO_TIME . \uniqid());
		$this->phpErrors = $phpErrors;
		$this->exception = $exception;
		foreach ($settings->trackServerVars as $varName) {
			if (isset($_SERVER[$varName])) {
				$this->serverVars[$varName] = $_SERVER[$varName];
			}
		}
		if ($settings->logGetVars) {
			$this->getVars = $_GET;
		}
		if ($settings->logPostVars) {
			$this->postVars = $_POST;
		}
		$this->markers = $markers;
		$this->stopwatchEntries = $stopwatchEntries;
		$this->peakMemoryUsage = memory_get_peak_usage();
		$this->scriptFilename = $_SERVER['SCRIPT_FILENAME'];
		$this->transactions = $transactions;
		// missing connection status and shutdown
	}

	/**
	 * connection speed bytes / sec
	 *
	 * @return float
	 */
	public function getConnectionSpeed()
	{
		return ($this->bytesIn + $this->bytesOut) / $this->runTime;
	}

}