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

namespace Foomo\Timer;

use Foomo\Timer as T;

/**
 * performance monitoring with markers nested and accumulative stop watches
 * 
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 * @internal
 */
class Simple {
	const BC_SCALE = 6;
	/**
	 * @internal
	 * @var bool
	 */
	public $useBCMath;
	/**
	 * @internal
	 * @var array array(array('name', 123.123456), array('name', 123.123456), ...)
	 */
	public $points = array();
	/**
	 * @internal
	 * @var array array('name' => array('start' => 123.123456, 'stop' => 123.1234567, 'nl' => 1, 'comment' => ...)) nl is the nesting level
	 */
	public $stopWatchPoints = array();

	private $maxMarkerLength = 70;

	/**
	 * you should use the static interface
	 * @internal
	 */
	public function __construct()
	{
		$this->useBCMath = function_exists('bcsub');
	}

	//---------------------------------------------------------------------------------------------
	// ~ internal public interface
	//---------------------------------------------------------------------------------------------

	/**
	 * @param string $topic
	 * @param null|string $comment
	 *
	 * @internal
	 */
	public function start($topic, $comment = null)
	{
		if (!isset($this->stopWatchPoints[$topic])) {
			$this->stopWatchPoints[$topic] = array();
		}
		$entry = array(
			'start' => self::microTime(),
			'stop' => null
		);
		if (!is_null($comment)) {
			$entry['comment'] = $comment;
		}
		$this->stopWatchPoints[$topic][] = $entry;
	}

	/**
	 * @param string $topic
	 *
	 * @internal
	 */
	public function stop($topic)
	{
		$found = null;
		$nl = 0;
		if (isset($this->stopWatchPoints[$topic])) {
			for ($i = count($this->stopWatchPoints[$topic]) - 1; $i > -1; $i--) {
				if (is_null($found) && is_null($this->stopWatchPoints[$topic][$i]['stop'])) {
					$found = $i;
					$this->stopWatchPoints[$topic][$i]['stop'] = self::microTime();
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

	/**
	 * @param array $topics
	 *
	 * @return array
	 *
	 * @internal
	 */
	public function accumulateStopWatchEntries(array $topics)
	{
		$ret = array();
		foreach($topics as $key => $topic) {
			if(!is_numeric($key)) {
				$stopWatchKey = $key;
			} else {
				$stopWatchKey = $topic;
			}
			if(isset($this->stopWatchPoints[$stopWatchKey])) {
				$ret[$topic] = array();
				foreach($this->stopWatchPoints[$stopWatchKey] as $stopWatchEntry) {
					$ret[$topic][] = $this->subtract($stopWatchEntry['stop'], $stopWatchEntry['start']);
				}
				$ret[$topic] = call_user_func_array(array($this, 'add'), $ret[$topic]);
			}
		}
		return $ret;
	}
	/**
	 * @param string $name
	 * @internal
	 */
	public function addMarker($name)
	{
		$this->points[] = array(self::microTime(), $name);
		$this->maxMarkerLength = max($this->maxMarkerLength, strlen($name));
	}

	public function clear()
	{
		$this->points = array();
		$this->stopWatchPoints = array();
		$this->maxMarkerLength = 70;
	}
	public function getStats()
	{
		$ret = __CLASS__ . ' ' . date('Y-m-d H:i:s', time()) . ' total time ' . $this->getTotalTime() . '' . PHP_EOL;
		for ($i = 1; $i < count($this->points); $i++) {
			$lineTitle = $this->points[$i][1];
			$lineTitle .= str_repeat('.', $this->maxMarkerLength - strlen($lineTitle)) . '|...';
			$ret .= $lineTitle . $this->subtract($this->points[$i][0], $this->points[$i - 1][0]) . PHP_EOL;
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
	/**
	 * @param bool $maxTime
	 * @internal
	 */
	public function logTime($maxTime = false)
	{
		$totalTime = $this->getTotalTime();
		if ($maxTime !== false) {
			if ($totalTime > $maxTime) {
				trigger_error($_SERVER['PHP_SELF'] . ' maximal script execution time (' . $maxTime . ' s) exceeded with ' . $totalTime . ' by ' . $this->subtract($totalTime, $maxTime), E_USER_WARNING);
			}
		} else {
			trigger_error($_SERVER['PHP_SELF'] . ' script execution time ' . $totalTime, E_USER_NOTICE);
		}
	}
	/**
	 * @return float
	 *
	 * @internal
	 */
	public function getTotalTime()
	{
		$startTime = $this->points[0][0];
		$stopTime = self::microTime();
		return (double) $this->subtract($stopTime, $startTime);
	}
	private static function microTime()
	{
		return microtime(true);
	}

	/**
	 * add all args
	 *
	 * @return int|mixed|string
	 */
	private function add()
	{
		$args = func_get_args();
		$sum = 0;
		while(count($args)>0) {
			$next = array_pop($args);
			if($this->useBCMath) {
				$sum = bcadd($sum, $next, self::BC_SCALE);
			} else {
				$sum += $next;
			}
		}
		return $sum;
	}

	/**
	 * subtract b from a
	 *
	 * @param mixed $a operand a
	 * @param mixed $b operand b
	 *
	 * @return mixed
	 */
	private function subtract($a, $b)
	{
		if ($this->useBCMath) {
			return bcsub($a, $b, self::BC_SCALE);
		} else {
			return $a - $b;
		}
	}
}
