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

use Foomo\Timer;
use Foomo\Config;
use Foomo\Module;

/**
 * collects data and writes logging entries
 * 
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
class Logger {
	/**
	 * number of errors recorded during the current process lifetime
	 *
	 * @var integer
	 */
	public $errorCount = 0;
	/**
	 * last error
	 *
	 * @var array
	 */
	public $lastError;
	/**
	 * @var array
	 */
	private $phpErrors = array();
	/**
	 *
	 * @var DomainConfig
	 */
	private $config;
	private $exception;
	private $enabled = false;
	private $transactions = array();
	private $processingTime;
	/**
	 *
	 * @var exit on an error - default is true, if you need another shutdown_func to be reached, set to false
	 */
	public $autoExitOnError = true;
	private function __construct()
	{

	}

	/**
	 * me singleton
	 *
	 * @return Logger
	 */
	public static function getInstance()
	{
		static $inst;
		if (!isset($inst)) {
			$inst = new self;
		}
		return $inst;
	}

	public static function bootstrap()
	{
		$loggerConfig = Config::getConf(\Foomo\Module::NAME, DomainConfig::NAME);
		if ($loggerConfig && $loggerConfig->enabled) {
			self::enable();
		} else {
			if (function_exists('apache_setenv')) {
				apache_setenv('FOOMO_LOG_ENTRY', Reader::DISABLED_ENTRY);
			}
		}
	}

	/**
	 * enable the logger - do not forget, that you still need a domainconfig
	 */
	public static function enable(DomainConfig $config = null)
	{
		if (\function_exists('apache_setenv')) {
			$inst = self::getInstance();
			if (!$inst->enabled) {
				if (is_null($config)) {
					$inst->config = Config::getConf(\Foomo\Module::NAME, DomainConfig::NAME);
				} else {
					$inst->config = $config;
				}
				\register_shutdown_function(array($inst, 'shutdownListener'));

				if (count($inst->config->trackErrors) > 0) {
					\set_error_handler(array($inst, 'handleError'));
				}
				if ($inst->config->trackExceptions) {
					\set_exception_handler(array($inst, 'handleException'));
				}
			} else {
				trigger_error(__CLASS__ . ' was already enabled', \E_USER_ERROR);
			}
		}
	}
	/**
	 * stringify and chop off a variable
	 *
	 * @param mixed $var
	 *
	 * @return string
	 */
	public function getVarAsString($var)
	{
		switch (true) {
			case is_null($var):
				$ret = 'null';
				break;
			case is_string($var):
				if (strlen($var) > 13) {
					$part = substr($var, 0, (isset($this->config->stringLogLength)?$this->config->stringLogLength:64)) . '...';
				} else {
					$part = $var;
				}
				$ret = '"' . $part . '"';
				break;
			case is_object($var):
				$ret = get_class($var);
				break;
			default:
				$ret = (string) $var;
		}
		return $ret;
	}

	public function handleError($errno, $errstr, $errfile, $errline, $errcontext = null)
	{
		// the errcontext might be interesting for the future
		/*
		if(error_reporting() == 0) {
			// sbdy put an @ in front what ever he was doing and let us hope he knew what he did
			// might be interesting for the future as well
		}
		*/
		$this->lastError = array('no' => $errno, 'str' => $errstr, 'file' => $errfile, 'line' => $errline, 'error_reporting' => error_reporting());
		$this->errorCount ++;
		if (in_array($errno, $this->config->trackErrorsAsIntegers) && $errno <= error_reporting()) {
			if (in_array($errno, $this->config->trackStrackTracesForErrorsAsIntegers)) {
				$rawTrace = \debug_backtrace(false);
				// var_dump($rawTrace);
				$trace = array();
				for ($i = 0; $i < count($rawTrace); $i++) {
					$rawStackEntry = $rawTrace[$i];
					if (isset($rawStackEntry['function'])) {
						if ($rawStackEntry['function'] == 'trigger_error' && !isset($rawStackEntry['class'])) {
							continue;
						} else if ($rawStackEntry['function'] == 'handleError' && isset($rawStackEntry['class']) && $rawStackEntry['class'] == __CLASS__) {
							continue;
						}
					}
					$stackEntry = array();
					foreach (array('file', 'line', 'function', 'object', 'args', 'type', 'class') as $optional) {
						if (isset($rawStackEntry[$optional])) {
							switch ($optional) {
								case 'object':
									$stackEntry[$optional] = $this->getVarAsString($rawStackEntry[$optional]);
									break;
								case 'args':
									$args = array();
									foreach ($rawStackEntry[$optional] as $arg) {
										$args[] = $this->getVarAsString($arg);
									}
									$stackEntry[$optional] = $args;
									break;
								case 'function':
									if (!isset($stackEntry['args'])) {
										$stackEntry['args'] = array();
									}
								default:
									if (isset($rawStackEntry[$optional])) {
										$stackEntry[$optional] = $rawStackEntry[$optional];
									}
							}
						}
					}

					$trace[] = $stackEntry;
				}
			} else {
				$trace = array();
			}
			$this->phpErrors[] = array(
				'no' => $errno,
				'str' => $errstr,
				'time' => microtime(true),
				'file' => $errfile,
				'line' => $errline,
				'trace' => $trace,
				'templStack' => \Foomo\Template::$stack
			);
			if ($this->config->replacePhpErrorLog) {
				\Foomo\Utils::appendToPhpErrorLog(
						'--------------------------------------------------------------------------------------------------------' . PHP_EOL .
						date('Y-m-d H:i:s') . PHP_EOL .
						Module::getView($this, 'error', $this->phpErrors[count($this->phpErrors) - 1])->render() .
						PHP_EOL
				);
			}
		}
		switch ($errno) {
			case E_USER_ERROR:
			case E_ERROR:
			case E_CORE_ERROR:
			case E_COMPILE_ERROR:
			case E_PARSE:
				// sometimes you have to do everything yourself ...
				if(!headers_sent()) {
					header(
						(isset($_SERVER['SERVER_PROTOCOL'])?$_SERVER['SERVER_PROTOCOL']:'HTTP/1.1') .
						'500 Internal Server Error'
					);
				}
				if($this->autoExitOnError) {
					exit(1);
				}
		}
		return $this->config->replacePhpErrorLog;
	}

	public static function transactionBegin($name, $comment = '')
	{
		self::getInstance()->transactions[] = array('name' => $name, 'comment' => $comment, 'status' => 'open', 'start' => microtime(true));
	}

	public static function transactionAbort($name, $comment = '')
	{
		self::getInstance()->applyStatusToOpenTransaction($name, 'aborted', $comment);
	}

	public static function transactionComplete($name, $comment = '')
	{
		self::getInstance()->applyStatusToOpenTransaction($name, 'complete', $comment);
	}

	public static function doneProcessing()
	{
		self::getInstance()->processingTime = microtime(true) - \Foomo\SYSTEM_START_MICRO_TIME;
	}

	private function applyStatusToOpenTransaction($name, $status, $comment)
	{
		for ($i = 0; $i < count($this->transactions); $i++) {
			if ($this->transactions[$i]['name'] == $name && $this->transactions[$i]['status'] == 'open') {
				$this->transactions[$i]['status'] = $status;
				$this->transactions[$i]['comment'] = (!empty($this->transactions[$i]['comment']) ? ', ' : '') . $comment;
				$this->transactions[$i]['stop'] = \microtime(true);
			}
		}
	}

	// @todo add to overwritten logging
	public function handleException(\Exception $e)
	{
		$this->exception = $e;
		\restore_error_handler();
		trigger_error('unhandled exception message: ' . $e->getMessage() . ', trace: ' . $e->getTraceAsString());
	}

	/**
	 * on shutdown the log entry is written by setting an env variable for apache
	 */
	public function shutdownListener()
	{

		$lastError = error_get_last();

		if (is_array($lastError) && in_array($lastError['type'], array(E_ERROR, E_CORE_ERROR, E_PARSE))) {
			// holy crap uncaught uncatchable fatal !!!
			// maybe scan, if it was recorder after all ...
			call_user_func_array(array($this, 'handleError'), $lastError);
		}

		$markers = Timer::getMarkers();
		$stopwatchEntries = Timer::getStopwatchEntries();

		$serialized = serialize($entry = new Entry($this->config, $this->phpErrors, $markers, $stopwatchEntries, $this->exception, $this->transactions, $this->processingTime));
		$gzipped = \gzdeflate($serialized);
		$base64 = \base64_encode($gzipped);
		apache_setenv('FOOMO_LOG_ENTRY', $base64);
		apache_setenv('FOOMO_SESSION_AGE', \Foomo\Session::getAge());
	}

	/**
	 * Gets an array of logging / mapping rules to translate a log file line to
	 * a log Entry. It ends with the last part, which can be parsed from the front
	 *
	 * @return array
	 */
	public static function getMapping()
	{
		$mapping = array();
		foreach (self::getLoggingRules() as $loggingRule) {
			$mapping[] = $loggingRule;
			if ($loggingRule['conf'] == '%u') {
				break;
			}
		}
		return $mapping;
	}

	public static function getLoggingRules()
	{
		return array(
			array('conf' => '%t', 'entryProp' => null, 'logPropName' => 'requestTime', 'comment' => 'Time the request was received (standard english format)',),
			array('conf' => '%{FOOMO_SESSION_ID}e', 'entryProp' => 'sessionId', 'comment' => 'foomo session id set as a session variable - only there, if you are running a php and the logger has to be enabled'),
			array('conf' => '%{FOOMO_SESSION_AGE}e', 'entryProp' => 'sessionAge', 'comment' => 'foomo session age'),
			array('conf' => '%H', 'entryProp' => null, 'logPropName' => 'requestProtocol', 'comment' => 'The request protocol'),
			array('conf' => '%s', 'entryProp' => 'httpStatus', 'comment' => 'Status. For requests that got internally redirected, this is the status of the *original* request --- %...>s  for the last.'),
			array('conf' => '%X', 'entryProp' => 'connectionStatus', 'comment' => 'Connection status when response is completed: X = connection aborted before the response completed, + = 	connection may be kept alive after the response is sent, - = 	connection will be closed after the response is sent.'),
			array('conf' => '%I', 'entryProp' => 'bytesIn', 'comment' => 'Bytes received, including request and headers, cannot be zero. You need to enable mod_logio  to use this.'),
			array('conf' => '%O', 'entryProp' => 'bytesOut', 'comment' => 'Bytes sent, including headers, cannot be zero. You need to enable mod_logio  to use this.'),
			array('conf' => '%a', 'entryProp' => null, 'logPropName' => 'remoteIp', 'comment' => 'Remote IP-address'),
			array('conf' => '%D', 'entryProp' => 'runTime', 'comment' => 'The time taken to serve the request, in microseconds.'),
			array('conf' => '%u', 'entryProp' => 'remoteUser', 'comment' => 'Remote user (from auth; may be bogus if return status (%s) is 401)'),
			array('conf' => '%f', 'entryProp' => null, 'logPropName' => 'file', 'comment' => 'Filename'),
			array('conf' => '\\"%{Referer}i\\"', 'entryProp' => null, 'logPropName' => 'referer', 'comment' => 'Referer'),
			array('conf' => '\\"%{User-Agent}i\\"', 'entryProp' => null, 'logPropName' => 'userAgent', 'comment' => 'User Agent'),
			array('conf' => '%{FOOMO_LOG_ENTRY}e', 'entryProp' => null, 'logPropName' => 'entry', 'comment' => 'the logged Foomo\Logger\Entry')
		);
	}

	public static function getLogFormatString()
	{
		$rules = array();
		foreach (self::getLoggingRules() as $loggingRule) {
			$prop = !is_null($loggingRule['entryProp']) ? $loggingRule['entryProp'] : $loggingRule['logPropName'];
			$rules[] = $prop . ': ' . $loggingRule['conf'];
		}
		return 'LogFormat "' . implode(Reader::LOG_DELIMITER, $rules) . '" foomoLogger';
	}

	/**
	 * return the file /path/to/your/root/var/<runmode>/logs/foomoLogger
	 *
	 * @return string
	 */
	public static function getLoggerFile()
	{
		return \Foomo\Config::getLogDir() . DIRECTORY_SEPARATOR . 'foomoLogger';
	}

}