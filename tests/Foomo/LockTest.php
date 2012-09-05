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
class LockTest extends \PHPUnit_Framework_TestCase {

	public function setUp() {
		$this->exposeTestScript();
	}

	public function tearDown() {
		$this->hideTestScript();
		parent::tearDown();
	}

	public function testGetLock() {
		$lockName1 = 'testLock1';
		$lockName2 = 'testLock2';
		$lockObtained = \Foomo\Lock::lockResource($lockName1, $blocking = true);
		$this->assertTrue($lockObtained, 'should be able to obtain first lock');

		//issue another process
		$sucess = file_get_contents(\Foomo\Utils::getServerUrl() . '/foomo/lock.php?lockName=' . $lockName1);

		$this->assertFalse($sucess != 'false', 'should not be able to obtain second lock');

		$lockReleased = \Foomo\Lock::releaseResource($lockName1);
		$this->assertTrue($lockReleased);

		// after the release it should work
		//issue another process
		$sucess = file_get_contents(\Foomo\Utils::getServerUrl() . '/foomo/lock.php?lockName=' . $lockName1);
		$this->assertFalse($sucess != 'true', 'should work after lock is released');
	}

	public function testLockAge() {
		$lockName1 = 'testLock1';
		$lockObtained = \Foomo\Lock::lockResource($lockName1, $blocking = false);
		sleep(3);
		$lockInfo = \Foomo\Lock::getLockInfo($lockName1);
		var_dump($lockInfo);
		$this->assertEquals(3, $lockInfo['lock_age']);
	}

	public function testGetInfo() {
		//get a lock from this process
		$lockName1 = 'testLock1';
		$lockObtained = \Foomo\Lock::lockResource($lockName1, $blocking = true);
		$this->assertTrue($lockObtained, 'should be able to obtain first lock');
		$info = \Foomo\Lock::getLockInfo($lockName1);
		var_dump($info);
		$this->assertTrue($info['is_locked'], 'should be locked after lock call');
		$this->assertTrue($info['caller_is_owner'], 'should owned by us');
		$this->assertLessThanOrEqual(5, $info['lock_age']);

		//release and lock from another process
		\Foomo\Lock::releaseResource($lockName1);
		
		//start second process but do not wait for return!
		
		self::callAsync(\Foomo\Utils::getServerUrl() . '/foomo/lock.php?lockName='.$lockName1.'&sleep=5');
		sleep(1);
		$info1 = \Foomo\Lock::getLockInfo($lockName1);
		var_dump($info1);
		$this->assertTrue($info1['is_locked'], 'should be locked after lock call');
		$this->assertFalse($info1['caller_is_owner'], 'we should not be owning it');
		
		$this->assertNotEquals(getmypid(), $info1['pid'], 'pids should not match'); 
		
	}
	
	
	
	
	private function exposeTestScript() {
		$file = __DIR__ . DIRECTORY_SEPARATOR . 'lock.php';
		symlink($file, \Foomo\Config::getHtdocsDir(\Foomo\Module::NAME) . DIRECTORY_SEPARATOR . 'lock.php');
	}

	private function hideTestScript() {
		unlink(\Foomo\Config::getHtdocsDir(\Foomo\Module::NAME) . DIRECTORY_SEPARATOR . 'lock.php');
	}

	private function callAsync($url) {
		$ch = \curl_init();
		curl_setopt($ch, CURLOPT_URL, $url );
		curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 1);
		curl_exec($ch);
		curl_close($ch);
	}
}