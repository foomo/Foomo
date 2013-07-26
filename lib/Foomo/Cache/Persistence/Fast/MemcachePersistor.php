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

/**
 * Memcache persistor
 *
 * example config in vhosts.conf
 * SetEnv "FOOMO_CACHE_FAST" "memcache::host=127.0.0.1,port=11211,persistent=true,weight=1,timeout=1,retry_interval=15,status=true;host=server2.com,port=11211"
 *
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
class MemcachePersistor implements \Foomo\Cache\Persistence\FastPersistorDirectInterface {

	/**
	 * @var Memcache
	 */
	private $memcache;
	private $serverIterator = 0;
	public $serverConfig;

	private function getId($id) {
		return \Foomo\ROOT . $id;
	}

	/**
	 *
	 * @param array $config array containing server config arrays, e.g. $config[0] = array('host'=>'...', 'port' = '...')
	 */
	public function __construct($config) {
		// $address, $port
		$this->memcache = new \Memcache();
		$config = $this->parseMemCacheConfig($config);
		//$server = array('host' => $host, 'port' => $port);
		$this->memcache->addServer($config['host'], $config['port'], $config['persistent'], $config['weight'], $config['timeout'], $config['retry_interval'], $config['status']);
		$this->memcache->connect($config['host'], $config['port']);
		$this->serverConfig = $config;
	}

	/**
	 * save resource
	 *
	 * @param Foomo\Cache\CacheResource $resource
	 *
	 * @return boolean
	 */
	public function save(\Foomo\Cache\CacheResource $resource) {
		$id = $this->getId($resource->id);
		return $this->memcache->set($id, $resource, ($resource->expirationTimeFast > 0 ? ($resource->expirationTimeFast - \time()) : 0));
	}

	/**
	 *
	 * @param Foomo\Cache\CacheResource $resource
	 * @param boolean $countHits counting hits not implemented for mrmcache persistor
	 * @return  Foomo\Cache\CacheResource $resource
	 */
	public function load(\Foomo\Cache\CacheResource $resource, $countHits = false) {
		$id = $this->getId($resource->id);
		return $this->memcache->get($id);
	}

	/**
	 * @param string $key
	 * @param string $value
	 * @return boolean
	 */
	public function directSave($key, $value) {
		return $this->memcache->set($key, $value);
	}

	/**
	 * @param string $key
	 * @return string mixed
	 */
	public function directLoad($key) {
		return $this->memcache->get($key);
	}

	/**
	 * deletes resource from cache
	 *
	 * @param Foomo\Cache\CacheResource $resource
	 *
	 * @return boolean
	 */
	public function delete(\Foomo\Cache\CacheResource $resource) {
		$id = $this->getId($resource->id);
		if ($this->memcache->get($id)) {
			return $this->memcache->delete($id);
		} else {
			return true;
		}
	}

	/**
	 * reset all cached resources, i.e. sets them to invalid. Note: system resources are not released
	 */
	public function reset() {
		$this->memcache->flush();
	}

	private function parseMemCacheConfig($config) {
		$serverConf = array();
		$serverConf['host'] = null;
		$serverConf['port'] = 11211;
		$serverConf['persistent'] = true;
		$serverConf['weight'] = 1;
		$serverConf['timeout'] = 1;
		$serverConf['retry_interval'] = 15;
		$serverConf['status'] = true;
		$properties = \explode(',', $config);
		foreach ($properties as $property) {
			$pair = \explode('=', $property);
			$name = \trim($pair[0]);
			$value = \trim($pair[1]);
			switch ($name) {
				case 'host':
					$serverConf[$name] = $value;
					break;
				case 'port':
					$serverConf[$name] = (int) $value;
					break;
				case 'persistent':
					if ($value == 'true')
						$serverConf[$name] = true;
					else
						$serverConf[$name] = false;
					break;
				case 'weight':
					$serverConf[$name] = (int) $value;
					break;
				case 'timeout':
					$serverConf[$name] = (int) $value;
					break;
				case 'retry_interval':
					$serverConf[$name] = (int) $value;
					break;
				case 'status':
					if ($value == 'true')
						$serverConf[$name] = true;
					else
						$serverConf[$name] = false;
					break;
			}
		}
		return $serverConf;
	}

}