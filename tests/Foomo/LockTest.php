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
	public function setUp()
	{
	}
	
	public function testGetLock()
	{
		$lockName1 = 'testLock1';
		$lockName2 = 'testLock2';
		
		$lockObtained = \Foomo\Lock::lock($lockName1, $blocking = true);
		$this->assertTrue($lockObtained, 'should be able to obtain first lock');
		
		$lockObtained2 = \Foomo\Lock::lock($lockName1, $blocking = false);
		
		$this->assertFalse($lockObtained2,'should not be able to obtain second lock');
		
		$lockReleased = \Foomo\Lock::release($lockName1);
		$this->assertTrue($lockReleased);
		
		
		// after the release it should work
		
		$lockObtained = \Foomo\Lock::lock($lockName1, $blocking = false);
		
		
	
		
		
	}
	
	
	public function testLockAge() {
		$lockName1 = 'testLock1';
		$lockObtained = \Foomo\Lock::lock($lockName1, $blocking = false);
		sleep(3);
		$lockInfo = \Foomo\Lock::getLockInfo($lockName1);
		$this->assertEquals(3, $lockInfo['lock_age']);
			
	}
	
}