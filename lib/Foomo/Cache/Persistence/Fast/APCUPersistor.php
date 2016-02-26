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
 * fast cache persistor using APC
 *
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
class APCUPersistor implements \Foomo\Cache\Persistence\FastPersistorDirectInterface {

	/**
	 * save
	 *
	 * @param \Foomo\Cache\CacheResource $resource
	 *
	 * @return bool
	 */
	public function save(\Foomo\Cache\CacheResource $resource)
	{
		static $i = 0;
		$i ++;
		$expiration = 0;
		if ($resource->expirationTimeFast != 0) {
			$expiration = $resource->expirationTimeFast - \time();
		}

		$id = $this->getId($resource->id);

		return apcu_store($id, $resource, $expiration);
	}
	private function getId($id)
	{
		return \Foomo\ROOT . Config::getMode() . $id;
	}

	/**
	 * load
	 *
	 * @param \Foomo\Cache\CacheResource $resource
	 * @param bool $countHits
	 *
	 * @return \Foomo\Cache\CacheResource
	 */
	public function load(\Foomo\Cache\CacheResource $resource, $countHits = false)
	{
		$loadedResource = apcu_fetch($this->getId($resource->id));
		if($loadedResource) {
			if ($countHits) {
				$loadedResource->hits++;
			}
			return $loadedResource;
		}
	}

	/**
	 * @param string $key
	 * @param string $value
	 * @return boolean
	 */
	public function directSave($key, $value)
	{
		return apcu_store($key, $value);
	}

	/**
	 * direct load
	 *
	 * @param string $key
	 *
	 * @return string mixed
	 */
	public function directLoad($key)
	{
		return apcu_fetch($key);
	}


	public function delete(\Foomo\Cache\CacheResource $resource)
	{
		if (!apcu_fetch($this->getId($resource->id))){
			return true;
		} else {
			return apcu_delete($this->getId($resource->id));
		}

	}


	public function __construct($config)
	{
		if (!function_exists('apcu_store')) {
			throw new \Exception('can not use this cache driver without apcu', 1);

		}
	}

	public function reset()
	{
		\apcu_clear_cache();
	}

}
