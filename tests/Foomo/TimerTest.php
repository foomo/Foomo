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
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
class TimerTest extends \PHPUnit_Framework_TestCase {
	public function testStopwatchAccumulation()
	{
		$timer = new Timer\Simple();
		$timer->start(__METHOD__);
		usleep(100000);
		$timer->stop(__METHOD__);
		$accumulated = $timer->accumulateStopWatchEntries(array(__METHOD__ => 'foo'));
		$this->assertGreaterThanOrEqual(0.1, $accumulated['foo']);
		$this->assertLessThanOrEqual(0.11, $accumulated['foo']);

		$timer = new Timer\Simple();
		$timer->start(__METHOD__);
		usleep(100000);
		$timer->stop(__METHOD__);
		$timer->start(__METHOD__);
		$timer->start(__METHOD__);
		usleep(100000);
		$timer->stop(__METHOD__);
		$timer->stop(__METHOD__);
		$accumulated = $timer->accumulateStopWatchEntries(array(__METHOD__ => 'foo'));
		$this->assertGreaterThanOrEqual(0.3, $accumulated['foo']);
		$this->assertLessThanOrEqual(0.31, $accumulated['foo']);
	}
}