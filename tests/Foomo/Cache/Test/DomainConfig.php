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

namespace Foomo\Cache\Test;

use Foomo\Config\AbstractConfig;

/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
class DomainConfig extends AbstractConfig {
	const NAME = 'Foomo.cacheTestConfig';
	/**
	 * hash of fast persistors
	 * 
	 * array('name' => 'configString')
	 * 
	 * @var array
	 */
	public $fastPersistors = array(
		'memcached' => 'memcache::host=127.0.0.1,port=11212,persistent=true,weight=1,timeout=1,retry_interval=15,status=true',
		'apc' => ''
	);
	/**
	 * hash of queryable persistors
	 * 
	 * array('name' => 'configString')
	 * 
	 * @var array
	 */
	public $queryablePersistors = array(
		'pdo' => 'pdo::mysql://root:@127.0.0.1/foomoCacheTestTest',
		'mongo' => 'mongo::mongodb://127.0.0.1:27017::database=foomoCacheTestTest'
	);
}