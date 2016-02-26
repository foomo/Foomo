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
class APCPersistor implements \Foomo\Cache\Persistence\FastPersistorDirectInterface {

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
		// $this->delete($resource);

		$id = $this->getId($resource->id);
		if(!apc_store($id, $resource, $expiration)) {

			////////////////////////////////////////////////////////////////////
			// this is a very ugly hack:
			//
			// apc can not store the same key multiple times in a row
			// it will "protect" you from doing so ...
			//
			// Thus if we can not store an entry, that was stored before:
			//   => we make a fake one with a unique id
			//   => add the one we actually wanted to add
			//   => remove the fake
			//
			////////////////////////////////////////////////////////////////////

			$fakeId = 'fake-' . $id . '-' . $i;
			$fakeIdSuccess = \apc_store($fakeId, 'bullshit ' . $i);
			if($fakeIdSuccess && \apc_store($id, $resource, $expiration)) {
				if(!apc_delete($fakeId)) {
					trigger_error('could not remove apc persisitor fake entry for ' . $resource->id . ' ' . $fakeId);
				}
			}

			return false; // resource was not saved
		}

		return true;
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
		$loadedResource = apc_fetch($this->getId($resource->id));
		if($loadedResource) {
			if ($countHits) {
				$loadedResource->hits++;
				// $this->save($loadedResource);
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
		return apc_store($key, $value);
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
		return apc_fetch($key);
	}


	public function delete(\Foomo\Cache\CacheResource $resource)
	{
		if (!apc_fetch($this->getId($resource->id))){
			 return true;   
		} else {
			 return apc_delete($this->getId($resource->id));
		}
		
	}


	public function __construct($config)
	{
		if (!function_exists('apc_store')) {
			var_dump($f=get_defined_functions());
			throw new \Exception('can not use this cache driver without apc', 1);

		}
	}

	public function reset()
	{
		\apc_clear_cache();
	}

}
