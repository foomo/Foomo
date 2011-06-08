<?php

namespace Foomo\Cache\Test;

use Foomo\Config\AbstractConfig;

class DomainConfig extends AbstractConfig {
	const NAME = 'Foomo.cacheTestConfig';
	public $fastPersistors = array('memcached' => '', 'apc' => '');
	public $queryablePersistors = array('pdo' => '', 'mongo' => '');
}