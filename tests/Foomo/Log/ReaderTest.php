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
		foreach($lines as $line) {
			$line = trim($line);
			if(empty($line)) {
				continue;
			}
			$parts = explode(Reader::LOG_DELIMITER, $line);
			$lastPart = end($parts);
			if($lastPart == Reader::DISABLED_ENTRY || $lastPart == READER::HTTP_EMPTY) {
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
	public function testReadStaticEntry()
	{
		$entry = Reader::extractLogEntryFromLine(
			'requestTime: [10/Dec/2013:21:56:36 +0100] sessionId: - sessionAge: - requestProtocol: HTTP/1.1 httpStatus: 200 connectionStatus: + bytesIn: 711 bytesOut: 1974 remoteIp: 192.168.56.1 runTime: 1195 remoteUser: - file: /var/www/paperRoll/var/test/htdocs/modulesVar/Foomo.Less-70/paperRoll-9b26e92c95f6b19d2c89405adc761216.css referer: "http://test.paperroll/a/51c310bc458873e708f23442" userAgent: "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/32.0.1700.41 Safari/537.36" entry: -',
			Logger::getMapping()
		);
		$this->assertNull($entry);
	}
	public function testReadFatalPendingEntry()
	{
		$entry = Reader::extractLogEntryFromLine(
			'requestTime: [10/Dec/2013:22:18:37 +0100] sessionId: 0952f9123296e14f7705fb7c1ce0efaee3275843 sessionAge: - requestProtocol: HTTP/1.1 httpStatus: 200 connectionStatus: + bytesIn: 452 bytesOut: 240 remoteIp: 192.168.56.1 runTime: 32376 remoteUser: - file: /var/www/paperRoll/modules/PaperRoll/htdocs/index.php referer: "-" userAgent: "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/32.0.1700.41 Safari/537.36" entry: pending',
			Logger::getMapping()
		);
		$this->assertInstanceOf('Foomo\\Log\\Entry', $entry);
		$this->assertEquals(500, $entry->httpStatus);
		$this->assertEquals('/var/www/paperRoll/modules/PaperRoll/htdocs/index.php', $entry->scriptFilename);
	}

}