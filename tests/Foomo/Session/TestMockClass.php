<?php
namespace Foomo\Session;

class TestMockClass {
	public $instanceId;
	public static $instanceCounter = 0;
	public function  __construct()
	{
		$this->instanceId = self::$instanceCounter ++;
	}
}