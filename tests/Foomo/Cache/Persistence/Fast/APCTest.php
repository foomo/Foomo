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

namespace Foomo\Cache\Persistence\Fast;

use Foomo\Cache\Manager;

class APCTest extends \PHPUnit_Framework_TestCase {

	private $resource;
	private $arguments;
	private $method;
	private $className;
	private $object;
	private $apcPersistor;

	public function testAPCBug() {
		$key = '_____________APC__________BUG_____ID';
		$var = 'test';
		$ttl = 0;
		for($i = 0;$i < 10;$i++) {
			$success = \apc_store($key, $var, $ttl);
			if($i > 0) {
				$this->assertFalse($success, 'remove the hack from the apc perisitor save, method ... they seem to have fixed it');
			}
		}
		for($i = 0;$i < 10;$i++) {
			\apc_store($key . '-hack', $var, $ttl);
			$success = \apc_store($key, $var, $ttl);
			if($i > 0) {
				$this->assertTrue($success, 'hack in apc perssistor seems to be broken');
			}
		}
	}
}