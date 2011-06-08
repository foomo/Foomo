<?php

/*
 * bestbytes-copyright-placeholder
 */

namespace Foomo\Cache\Persistence;

interface QueryablePersistorInterface extends \Foomo\Cache\Persistence\FastPersistorInterface {

	/**
	 * finds all resources matching expression
	 *
	 * @param string $resourceName
	 * @param \Foomo\Cache\Persistence\Expr $expr
	 * @param integer $limit
	 * @param integer $offset
	 *
	 * @return CacheResourceIterator
	 */
	public function query($resourceName, $expr, $limit, $offset);

	/**
	 * provides a persistor specific expression interpretation of the query expression
	 *
	 * @param string $resourceName;
	 * @param \Foomo\Cache\Persistence\Expr $expression
	 * 
	 * @return mixed
	 */
	public function getExpressionInterpretation($resourceName, $expression);

	/**
	 * get all cached resource names
	 *
	 * @return array of resource names
	 */
	public function getCachedResourceNames();

	/**
	 * check if storage structure (table) exists for resource
	 *
	 * @param string $resourceName
	 *
	 * @return bool
	 */
	public function storageStructureExists($resourceName);

	/**
	 * validates storage structure against resource annotation
	 *
	 * @param string $resourceName
	 * @param bool $verbose do we output to stdout
	 *
	 * @return boolean true if valid
	 */
	public function validateStorageStructure($resourceName, $verbose = false);
}
