<?php

namespace Foomo\Cache\Persistence\Fast;

/**
 * Memcache persistor
 *
 *
 * example config in vhosts.conf
 * SetEnv "FOOMO_CACHE_FAST" "memcache::host=127.0.0.1,port=11211,persistent=true,weight=1,timeout=1,retry_interval=15,status=true;host=server2.com,port=11211"

 */
class MemcachePersistor implements \Foomo\Cache\Persistence\FastPersistorInterface {

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
	 * deletes resource from cache
	 *
	 * @param Foomo\Cache\CacheResource $resource
	 *
	 * @return boolean
	 */
	public function delete(\Foomo\Cache\CacheResource $resource) {
		$id = $this->getId($resource->id);
		return $this->memcache->delete($id);
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