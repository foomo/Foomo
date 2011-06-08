<?php

namespace Foomo\Cache\Persistence\Fast;

use Foomo\Cache\Manager;

class MemcacheTest extends \Foomo\Cache\AbstractBaseTest {

	private $resource;
	private $arguments;
	private $method;
	private $className;
	private $object;
	private $apcPersistor;

	
	public function testLongId() {
		$memcache = \Foomo\Cache\Manager::getFastPersistor();
		$longId = '12345678901234567890-1234567890234567890-1234567890qwertyuiopsdfghjkl;xcvbnsdfghjkwertyuqwertyuasdfghjklqwertyuioaaaaaaaaaaaaaaaaaaaaaaadddddddddddddddddddddddddddddddddddddddddddddddhjksfgjaksfgahksfgggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggg';
		$resource = \Foomo\Cache\Proxy::getEmptyResource('Foomo\Cache\MockObjects\SampleResources', 'test', array());
		$resource->id = $longId;

		$resource->value = 'I am a value';
		$memcache->save($resource);

		$loaded = $memcache->load($resource);

		$this->assertEquals($resource, $loaded);

	}

}