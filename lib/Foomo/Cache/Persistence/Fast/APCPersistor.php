<?php

namespace Foomo\Cache\Persistence\Fast;

/**
 * fast cache persistor using APC
 */
class APCPersistor implements \Foomo\Cache\Persistence\FastPersistorInterface {

	public function save(\Foomo\Cache\CacheResource $resource) {
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
		return \Foomo\ROOT . $id;
	}
	public function load(\Foomo\Cache\CacheResource $resource, $countHits = false) {

		$loadedResource = apc_fetch($this->getId($resource->id));
		if($loadedResource) {
			if ($countHits) {
				$loadedResource->hits++;
				// $this->save($loadedResource);
			}
			return $loadedResource;
		}
	}

	public function delete(\Foomo\Cache\CacheResource $resource) {
		return apc_delete($this->getId($resource->id));
	}

	public function __construct($config) {
		if (!function_exists('apc_store')) {
			throw new Exception('can not use this cach driver without apc', 1);
		}
	}

	public function reset()
	{
		\apc_clear_cache('user');
	}

}