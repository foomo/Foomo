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

use PHPUnit_Framework_TestCase as TestCase;

/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
class ReaderTest extends TestCase {
	/**
	 * @var Reader
	 */
	private $reader;
	public function setUp()
	{
		$this->reader = new Reader(Mock::getMockLog());
	}
	public function testIteration()
	{
		$i = 0;
		foreach($this->reader as $entry) {
			$this->assertTrue($entry instanceof Entry);
			$i ++;
		}
		$lines = explode(PHP_EOL, file_get_contents(Mock::getMockLog()));
		$count = 0;
		$needle = '-';
		foreach($lines as $line) {
			if(substr($line, -strlen($needle))== $needle || empty($line)) {
				continue;
			}
			$count ++;
		}
		$this->assertTrue($i == $count, 'log entry count missed ' . $count . ' '. $i);
	}
	public function testFilteredIteration()
	{
		$sessionId = null;
		foreach($this->reader as $entry) {
			if($entry->sessionId) {
				$sessionId = $entry->sessionId;
				break;
			}
		}
		if(is_null($sessionId)) {
			$this->fail('could not find a test session');
		}
		$this->reader->setFilter(function(Entry $e) use ($sessionId) {
			if($e->sessionId == $sessionId) {
				return true;
			} else {
				return false;
			}
		});
		foreach($this->reader as $e) {
			$this->assertEquals($sessionId, $e->sessionId);
		}
	}
}