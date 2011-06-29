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
 */
class Timer {
	/**
	 * call will be only added to stats in debug mode
	 */
	const LOG_LEVEL_DEBUG = 'debug';
	/**
	 * call will always be added to stats
	 */
	const LOG_LEVEL_PRODUCTION = 'production';

	public static function addMarker($msg, $logLevel = 'production')
	{
		if ($logLevel == self::LOG_LEVEL_DEBUG && in_array(Foomo\Config::getMode(), array('development', 'test'))) {
			return;
		} else {
			self::getTimerInstance($msg)->addMarker($msg);
		}
	}

	/**
	 * start over
	 *
	 */
	public static function reset()
	{
		self::getTimerInstance(true);
	}

	/**
	 * get a plain text report
	 *
	 * @return string
	 */
	public static function getStats()
	{
		//self::getTimerInstance()->stop();
		return self::getTimerInstance()->debugPlain();
	}

	/**
	 * get a timer
	 *
	 * @return PhpTimer
	 *
	 */
	public static function start($topic, $comment = null)
	{
		self::getTimerInstance()->startStopwatch($topic, $comment);
	}

	public static function stop($topic)
	{
		self::getTimerInstance()->stopStopwatch($topic);
	}

	private static function getTimerInstance($newTimer = false)
	{
		static $timer = null;
		if (!isset($timer) || $newTimer === true) {
			$timer = new PhpTimer();
			$timer->start();
		}
		return $timer;
	}

	/**
	 * will trigger into your php log, if $maxTime was exceeded
	 *
	 * @param float $maxTime time in seconds that must not be exceeded | false if not to be triggered
	 */
	public static function logTime($maxTime = false)
	{
		self::getTimerInstance()->logTime($maxTime);
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
		$fp = fopen($logFile, 'a+');
		fwrite($fp, '-------------------------------------------------------------' . PHP_EOL . $_SERVER['SCRIPT_FILENAME'] . PHP_EOL . self::getStats() . PHP_EOL);
		fclose($fp);
	}

	/**
	 * @internal
	 * @return array
	 */
	public static function getMarkers()
	{
		return self::getTimerInstance()->getMarkers();
	}

	/**
	 * @internal
	 * @return array
	 */
	public static function getStopwatchEntries()
	{
		return self::getTimerInstance()->getStopwatchEntries();
	}

}

/**
 * very simple tool for performance comtrol in a project use Foomo\Timer
 *
 * @access internal
 * @deprecated
 */
class PhpTimer {

	// array to store the information that we collect during the script
	// this array will be manipulated by the functions within our object
	private $points = array();
	private $stopWatchPoints = array();
	private $totalTime = 0;
	private $useBcMath;

	public function __construct()
	{
		if (function_exists('bcsub')) {
			$this->useBcMath = true;
		} else {
			$this->useBcMath = false;
		}
	}

	/**
	 * @internal
	 * @return array
	 */
	public function getMarkers()
	{
		return $this->points;
	}

	/**
	 * @internal
	 * @return array
	 */
	public function getStopwatchEntries()
	{
		return $this->stopWatchPoints;
	}

	// call this function at the beginning of the script
	public function start()
	{
		// see the addmarker() function later on
		$this->addmarker("Start");
	}

	public function startStopwatch($topic, $comment = null)
	{
		if (!isset($this->stopWatchPoints[$topic])) {
			//$this->stopWatchPoints[$topic] = array();
		}
		$entry = array(
			'start' => microtime(true),
			'stop' => null
		);
		if (!is_null($comment)) {
			$entry['comment'] = $comment;
		}
		$this->stopWatchPoints[$topic][] = $entry;
	}

	public function stopStopwatch($topic)
	{
		if (isset($this->stopWatchPoints[$topic])) {
			$nl = 0;
			$found = null;
			for ($i = count($this->stopWatchPoints[$topic]) - 1; $i > -1; $i--) {
				if (is_null($found) && is_null($this->stopWatchPoints[$topic][$i]['stop'])) {
					$found = $i;
					$this->stopWatchPoints[$topic][$i]['stop'] = microtime(true);
				} else if (!is_null($found)) {
					if (is_null($this->stopWatchPoints[$topic][$i]['stop'])) {
						$nl++;
					} else {
						break;
					}
				}
			}
		}
		if (!is_null($found) && $nl > 0) {
			$this->stopWatchPoints[$topic][$found]['nl'] = $nl;
		}
	}

	// call this function at the end of the script
	/**
	 * @deprecated
	 */
	public function stop()
	{
		//$this->addmarker("Stop");
	}

	private function updateTotalTime()
	{
		$startTime = $this->points[0][0];
		$stopTime = $this->points[count($this->points) - 1][0];
		if ($this->useBcMath)
			$tot = bcsub($stopTime, $startTime, 6);
		else
			$tot = $stopTime - $startTime;
		$this->totalTime = $tot;
	}

	// this function is called to add a marker during the scripts execution
	// it requires a descriptive name
	public function addmarker($name)
	{
		$markertime = microtime(true);
		// $ae (stands for Array Elements) will contain the number of elements
		// currently in the $points array
		$ae = count($this->points);
		// store the timestamp and the descriptive name in the array
		$this->points[$ae][0] = $markertime;
		$this->points[$ae][1] = $name;
	}

	// end function addmarker()
	public function clear()
	{
		$this->points = array();
	}

	// this function simply give the difference in seconds betwen the start of the script and
	// the end of the script
	function showtime(&$ret)
	{
		if ($this->useBcMath)
			$ret .= bcsub($this->points[count($this->points) - 1][0], $this->points[0][0], 6);
		else
			$ret .= $this->points[count($this->points) - 1][0] - $this->points[0][0];
	}

	// end function showtime()
	// this function displays all of the information that was collected during the
	// course of the script
	public function debug()
	{
		return '<pre>' . $this->debugPlain() . '</pre>';
	}

	// end function debug()
	public function debugPlain()
	{
		$this->updateTotalTime();
		$tot = $this->totalTime;
		$ret = __CLASS__ . ' ' . date('Y-m-d H:i:s', time()) . ' total time ' . $tot . '' . PHP_EOL;
		for ($i = 1; $i < count($this->points); $i++) {
			$lineTitle = $this->points[$i][1];

			while (strlen($lineTitle) < 50) {
				$lineTitle .= '.';
			}
			$lineTitle .= '|...';
			$ret .= $lineTitle;
			if ($this->useBcMath)
				$ret .= bcsub($this->points[$i][0], $this->points[$i - 1][0], 6);
			else
				$ret .= $this->points[$i][0] - $this->points[$i - 1][0];
			$ret .= PHP_EOL;
		}
		if (count($this->stopWatchPoints) > 0) {
			$ret .= PHP_EOL . 'stop watch entries:' . PHP_EOL;
			foreach ($this->stopWatchPoints as $topic => $stopWatchPoints) {
				$ret .= $topic . ' : ' . PHP_EOL;
				$sumTime = 0;
				foreach ($stopWatchPoints as $stopWatchPoint) {
					if (!isset($stopWatchPoint['nl'])) {
						$nl = 0;
					} else {
						$nl = $stopWatchPoint['nl'];
					}
					$lapTime = $stopWatchPoint['stop'] - $stopWatchPoint['start'];
					$sumTime += $lapTime;
					$ret .=
							str_repeat('  ', $nl + 1) .
							' : ' . ( $lapTime ) .
							(($nl > 0) ? ' @ nesting level ' . $nl : '') .
							( isset($stopWatchPoint['comment']) ? ' (' . $stopWatchPoint['comment'] . ')' : '') .
							PHP_EOL
					;
				}
				if (count($stopWatchPoints) > 1) {
					$ret .= '  sum: ' . $sumTime . PHP_EOL;
				}
			}
		}
		return $ret;
	}

	public function logTime($maxTime = false)
	{
		if ($maxTime !== false) {
			if ($this->totalTime > $maxTime) {
				if ($this->useBcMath) {
					$diff = bcsub($this->totalTime, $maxTime, 6);
				} else {
					$diff = $this->totalTime - $maxTime;
				}
				trigger_error($_SERVER['PHP_SELF'] . ' maximal script execution time (' . $maxTime . ' s) exceeded with ' . $this->totalTime . ' by ' . $diff, E_USER_WARNING);
			}
		} else {
			trigger_error($_SERVER['PHP_SELF'] . ' script execution time ' . $this->totalTime, E_USER_NOTICE);
		}
	}

}
