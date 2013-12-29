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

namespace Foomo;

/**
 * performance monitoring with markers nested and accumulative stop watches
 * 
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
class Timer {
	/**
	 * call will be only added to stats in debug / development mode
	 */
	const LOG_LEVEL_DEBUG = 'debug';
	/**
	 * call will always be added to stats
	 */
	const LOG_LEVEL_PRODUCTION = 'production';

	//---------------------------------------------------------------------------------------------
	// ~ public static interface
	//---------------------------------------------------------------------------------------------

	/**
	 * add a marker to see where you came by and when
	 *
	 * @param string $msg
	 * @param string $logLevel
	 */
	public static function addMarker($msg, $logLevel = self::LOG_LEVEL_PRODUCTION)
	{
		if ($logLevel == self::LOG_LEVEL_DEBUG && Config::isProductionMode()) {
			return;
		} else {
			self::getInstance()->addMarker($msg);
		}
	}
	/**
	 * start over
	 */
	public static function reset()
	{
		self::getInstance()->clear();
	}
	/**
	 * get a plain text report
	 *
	 * @return string
	 */
	public static function getStats()
	{
		return self::getInstance()->getStats();
	}
	/**
	 * start stopwatch - nesting is not a problem
	 *
	 * @param string $topic
	 * @param string $comment
	 */
	public static function start($topic, $comment = null)
	{
		self::getInstance()->start($topic, $comment);
	}
	/**
	 * stop measuring
	 *
	 * @param $topic
	 */
	public static function stop($topic)
	{
		self::getInstance()->stop($topic);
	}
	/**
	 * will trigger into your php log, if $maxTime was exceeded
	 *
	 * @param float|bool $maxTime time in seconds that must not be exceeded | false if not to be triggered
	 */
	public static function logTime($maxTime = false)
	{
		self::getInstance()->logTime($maxTime);
	}
	/**
	 * write stats to a file, by default to the php_error log file
	 *
	 * @param string $logFile the /path/to/logfile - if none is passed the error_log file from the ini settings will be used
	 */
	public static function writeStatsToFile($logFile = null)
	{
		if (!$logFile) {
			$logFile = ini_get('error_log');
		}
		file_put_contents(
			$logFile,
			'-------------------------------------------------------------' . PHP_EOL .
			$_SERVER['SCRIPT_FILENAME'] . PHP_EOL . self::getStats() . PHP_EOL,
			FILE_APPEND
		);
	}
	/**
	 * @internal
	 * @return array
	 */
	public static function getMarkers()
	{
		return self::getInstance()->points;
	}
	/**
	 * @internal
	 * @return array
	 */
	public static function getStopwatchEntries()
	{
		return self::getInstance()->stopWatchPoints;
	}
	/**
	 * accumulate stop watch entries
	 *
	 * @param array $topics array('timerName' => 'myName', )
	 *
	 * @return array array('topic' => 123.456, 'otherTopic' => ..., ...)
	 */
	public static function accumulateStopWatchEntries(array $topics)
	{
		return self::getInstance()->accumulateStopWatchEntries($topics);
	}

	/**
	 * use the static methods instead
	 * @return Timer\Simple
	 * @internal
	 */
	public static function getInstance()
	{
		static $timer = null;
		if (is_null($timer)) {
			$timer = new Timer\Simple();
			$timer->addMarker('start');
		}
		return $timer;
	}

}