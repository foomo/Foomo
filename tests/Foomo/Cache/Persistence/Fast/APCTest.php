<?php

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