<?php

/*
 * bestbytes-copyright-placeholder
 */

namespace Foomo\Cache\Persistence;

use Foomo\Cache\Resource;

interface FastPersistorInterface {

	/**
	 * get the persistor
	 */
	public function __construct($config);
	/*
	 * Save a resource into cache
	 * 
	 * @return boolean return true if succesful
	 */

	public function save(\Foomo\Cache\CacheResource $resource);

	/**
	 * Retrieve a resource from cache
	 * 
	 * @param string $id
	 *
	 * @param boolean $countHits should monitor number of times record was loaded
	 *
	 * @return Foomo\Cache\Resource
	 */
	public function load(\Foomo\Cache\CacheResource $resource, $countHits = false);

	/**
	 * delete a resource from cache
	 * 
	 *
	 * @param string $id
	 *
	 * @return boolean true if successful
	 */
	public function delete(\Foomo\Cache\CacheResource $resource);

	/**
	 * remove EVERYTHING
	 */
	public function reset();
}
