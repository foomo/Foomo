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

namespace Foomo\Cache\Persistence\Queryable;

/**
 * A non queryable cache peristor implementation. Fallback implementatoin if no queryable database is available.
 *
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author frederik <frederik@bestbytes.de>
 */
class NonQueryableCachePersistor implements \Foomo\Cache\Persistence\QueryablePersistorInterface {

	public function __construct($persistorConfig) {		
	}

	public function save(\Foomo\Cache\CacheResource $resource) {
		return true;
	}

	public function load(\Foomo\Cache\CacheResource $resource, $countHits = false) {
		return null;
	}

	public function delete(\Foomo\Cache\CacheResource $resource) {
		return true;
	}

	public function reset($resourceName = null, $recreateStructures = true) {
		return true;
	}

	
	/**
	 * finds all resources matching expression
	 *
	 * @param string $resourceName
	 * @param Foomo\Cache\Persistence\Expr $expr
	 * @param integer $limit
	 * @param integer $offset
	 *
	 * @return Foomo\Cache\CacheResourceIterator
	 */
	public function query($resourceName, $expr, $limit, $offset) {
		return new \Foomo\Cache\CacheResourceIterator();
	}

	/**
	 * provides a persistor specific expression interpretation of the query expression
	 *
	 * @param string $resourceName;
	 * @param \Foomo\Cache\Persistence\Expr $expression
	 *
	 * @return mixed
	 */
	public function getExpressionInterpretation($resourceName, $expression) {
		return $expression;
	}

	/**
	 * get all cached resource names
	 *
	 * @return array of resource names
	 */
	public function getCachedResourceNames() {
		return array();
	}

	/**
	 * check if storage structure (table) exists for resource
	 *
	 * @param string $resourceName
	 *
	 * @return bool
	 */
	public function storageStructureExists($resourceName) {
		return true;
	}

	/**
	 * validates storage structure against resource annotation
	 *
	 * @param string $resourceName
	 * @param bool $verbose do we output to stdout
	 *
	 * @return boolean true if valid
	 */
	public function validateStorageStructure($resourceName, $verbose = false) {
		return true;
	}
}
