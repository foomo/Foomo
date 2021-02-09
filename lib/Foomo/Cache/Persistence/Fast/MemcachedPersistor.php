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

use Foomo\Config;

/**
 * Memcached persistor
 *
 * example config in vhosts.conf
 * SetEnv "FOOMO_CACHE_FAST" "memcached::host=127.0.0.1,port=11211,persistentId=anUniqueId,weight=1"
 *
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
class MemcachedPersistor implements \Foomo\Cache\Persistence\FastPersistorDirectInterface
{

	/**
	 * @var \Memcached
	 */
	private $memcached;
	public $serverConfig;

	private function getId($id)
	{
		return \Foomo\ROOT . Config::getMode() . $id;
	}

	/**
	 *
	 * @param array $config array containing server config arrays, e.g. $config[0] = array('host'=>'...', 'port' = '...')
	 */
	public function __construct($config)
	{
		// $address, $port
		$config = $this->parseMemCachedConfig($config);
		$this->serverConfig = $config;
		$this->memcached = new \Memcached($config['persistentId']);
		if (!count($this->memcached->getServerList())) {
			//$server = array('host' => $host, 'port' => $port);
			$this->memcached->addServer($config['host'], $config['port'], $config['weight']);
		}
	}

	/**
	 * save
	 *
	 * @param \Foomo\Cache\CacheResource $resource
	 *
	 * @return bool
	 */
	public function save(\Foomo\Cache\CacheResource $resource)
	{
		$id = $this->getId($resource->id);
		return $this->memcached->set($id, $resource, ($resource->expirationTimeFast > 0 ? ($resource->expirationTimeFast - \time()) : 0));
	}

	/**
	 * load resource
	 * @param \Foomo\Cache\CacheResource $resource
	 * @param boolean $countHits counting hits not implemented for memcache persistor
	 * @return \Foomo\Cache\CacheResource $resource
	 */

	public function load(\Foomo\Cache\CacheResource $resource, $countHits = false)
	{
		$id = $this->getId($resource->id);
		return $this->memcached->get($id);
	}

	/**
	 * @param string $key
	 * @param string $value
	 * @return boolean
	 */
	public function directSave($key, $value)
	{
		return $this->memcached->set($key, $value);
	}

	/**
	 * @param string $key
	 * @return string mixed
	 */
	public function directLoad($key)
	{
		return $this->memcached->get($key);
	}

	/**
	 * deletes resource from cache
	 *
	 * @param \Foomo\Cache\CacheResource $resource
	 *
	 * @return boolean
	 */
	public function delete(\Foomo\Cache\CacheResource $resource)
	{
		$id = $this->getId($resource->id);
		if ($this->memcached->delete($id)) {
			return true;
		} else {
			return $this->memcached->getResultCode() == \Memcached::RES_NOTFOUND ? true : false;
		}
	}

	/**
	 * reset all cached resources, i.e. sets them to invalid. Note: system resources are not released
	 */
	public function reset()
	{
		$this->memcached->flush();
	}

	private function parseMemCachedConfig($config)
	{
		$serverConf = array();
		$serverConf['host'] = null;
		$serverConf['port'] = 11211;
		$serverConf['persistentId'] = 'foomo';
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
					$serverConf[$name] = (int)$value;
					break;
				case 'persistentId':
					$serverConf[$name] = "$value";
					break;
				case 'weight':
					$serverConf[$name] = (int)$value;
					break;
			}
		}
		return $serverConf;
	}
}