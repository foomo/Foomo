<?php

namespace Foomo\Log;

use PHPUnit_Framework_TestCase as TestCase;

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