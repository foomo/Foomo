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

namespace Foomo\Cache\Persistence;

use Foomo\Cache\Resource;

/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
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
	 * @param \Foomo\Cache\CacheResource $resource
	 * @param bool $countHits
	 *
	 * @return \Foomo\Cache\CacheResource
	 */
	public function load(\Foomo\Cache\CacheResource $resource, $countHits = false);
	/**
	 * delete a resource from cache
	 *
	 *
	 * @param \Foomo\Cache\CacheResource $resource
	 *
	 * @return boolean true if successful
	 */
	public function delete(\Foomo\Cache\CacheResource $resource);

	/**
	 * remove EVERYTHING
	 */
	public function reset();
}
