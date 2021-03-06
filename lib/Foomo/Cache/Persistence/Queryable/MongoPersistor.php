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
 * A Mongo db persistor implementation.
 *
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author bostjanm <bostjan.marusic@bestbytes.de>
 */
class MongoPersistor implements \Foomo\Cache\Persistence\QueryablePersistorInterface
{
	/**
	 * name of the collection that stores  resource names (  since in some cases the resource name can not be
	 * deducted from the table name due to the 64 chars limitation in mySQL)
	 */
	const RESOURCE_NAMES_COLLECTION = 'CACHED_RESOURCE_NAMES';
	/**
	 * mongo database handle
	 * @var \MongoClient
	 */
	public $mongoClient;
	/**
	 *
	 * @var string db name
	 */
	protected $databaseName = 'foomoCachePersistor';

	/**
	 * @var MongoDB
	 */
	protected $database = null;
	/**
	 * host url (urls, comma separated)
	 *
	 * @var string
	 */
	protected $host = 'mongodb://localhost/';

	/**
	 *
	 * @param string $persistorConfig mongo persistor config line
	 */
	public function __construct($persistorConfig)
	{
		$this->parseConfig($persistorConfig);
		$this->connect($persistorConfig);

	}

	private function parseConfig($persistorConfig)
	{
		$confArray = explode('::', $persistorConfig);
		foreach ($confArray as $confPart) {
			$confPart = trim($confPart);
			if (strpos($confPart, 'mongodb://') !== false) {
				$this->host = $confPart;
			} else if (strpos($confPart, 'database=') !== false) {
				$this->databaseName = str_replace('database=', '', $confPart);
			}
		}
	}

	/**
	 * create client connection
	 * @param string $persistorConfig
	 * @return boolean
	 */
	protected function connect($persistorConfig)
	{
		try {
			$this->mongoClient = new \MongoClient($this->host);
			if (is_null($this->database)) {
				$db = $this->databaseName;
				$this->database = $this->mongoClient->$db;
			}
			return true;
		} catch (\Exception $e) {
			$this->mongoClient = null;
			$this->database = null;
			// if we can not connect die here!

			trigger_error(__CLASS__ . __METHOD__ . $e->getMessage(), \E_USER_ERROR);
		}
	}

	/**
	 * get resource name for collection
	 * @param string $collectionName
	 * @return string
	 */
	public static function resourceNameFromCollectionName($collectionName)
	{
		$collectionName = \str_replace("___", "::", $collectionName);
		$collectionName = \str_replace("__", "->", $collectionName);
		$collectionName = \str_replace("_", "\\", $collectionName);
		return $collectionName;
	}


	/**
	 * save into mongo
	 *
	 * @param \Foomo\Cache\CacheResource $resource
	 *
	 * @return boolean
	 */
	public function save(\Foomo\Cache\CacheResource $resource)
	{
		try {
			$document = \get_object_vars($resource);
			$document['value'] = \serialize($resource->value);
			$document['queriableProperties'] = $this->preparePropertiesForQueryOperations($resource);
			$collection = $this->getMongoCollection($document);
			$collection->ensureIndex(array("id" => 1), array("unique" => 1, "dropDups" => 1));

			// mongo has no locking - using an atomic upsert operation here
			return $collection->update(array('id' => $document['id']), array('$set' => $document), array('upsert' => true));
		} catch (\Exception $e) {
			\trigger_error(__METHOD__ . ' : ' . $e->getMessage(), \E_USER_WARNING);
			return false;
		}
	}


	private function preparePropertiesForQueryOperations(\Foomo\Cache\CacheResource $resource)
	{
		$serializedProperties = array();
		foreach ($resource->properties as $propName => $propValue) {
			$serializedProp = $this->getQueryableRepresentation($propValue);
			$serializedProperties[$propName] = $serializedProp;
		}
		return $serializedProperties;
	}

	public static function getQueryableRepresentation($propertyValue)
	{

		if (is_object($propertyValue)) {
			return self::getObjectFingerprint($propertyValue);
		} else if (is_array($propertyValue)) {
			return self::getObjectFingerprint($propertyValue);
		} else if (is_bool($propertyValue)) {
			return $propertyValue;
		} else if (is_float($propertyValue)) {
			return $propertyValue;
		} else if (is_double($propertyValue)) {
			return $propertyValue;
		} else if (is_int($propertyValue)) {
			return $propertyValue;
		} else if (is_long($propertyValue)) {
			return $propertyValue;
		} else if (is_string($propertyValue)) {
			return self::getObjectFingerprint($propertyValue);
		} else if (!isset($propertyValue)) {
			//null treated as object or string
			return self::getObjectFingerprint($propertyValue);
		} else { //default is object
			return self::getObjectFingerprint($propertyValue);
		}
	}

	/**
	 * compute a fingerpring of an object. for objects that implement __toString returns md5 of __toString's return value
	 *
	 * otherwise returns md5 of serialized object, which may be  slower for large objects
	 *
	 * @param mixed $object
	 *
	 * @return string
	 */
	private static function getObjectFingerprint($object)
	{
		switch (true) {
			case is_object($object):
				if (method_exists($object, '__toString')) {
					$ret = $object->__toString();
				} else {
					$ret = serialize($object);
				}
				break;
			case is_array($object):
				$ret = serialize($object);
				break;
			case is_string($object):
				$ret = $object;
				break;
			case is_null($object):
				$ret = '';
				break;
			default:
				trigger_error('that was unexpected ' . var_export($object, true), \E_USER_ERROR);
		}
		if (strlen($ret) > 32) {
			return md5($ret);
		} else {
			return $ret;
		}
	}

	/**
	 * gets a mongo collection, i.e. mongo/db/collection object
	 * creates an id unique index when collelction first created
	 *
	 * @param array $document
	 * @return \MongoCollection
	 */
	private function getMongoCollection($document)
	{
		$collection = $this->collectionNameFromResourceName($document['name']);
		return $this->database->$collection;
	}

	/**
	 * map resource name to a valid mongo collection name
	 *
	 * @param string $resourceName cache resource name
	 *
	 * @return string
	 */
	public static function collectionNameFromResourceName($resourceName)
	{
		$resourceName = \str_replace("\\", "_", $resourceName);
		$resourceName = \str_replace("->", "__", $resourceName);
		$resourceName = \str_replace("::", "___", $resourceName);
		return $resourceName;
	}

	/**
	 * load document by id
	 * @param array $document
	 * @return array|null
	 */
	private function loadDocument($document)
	{
		$collection = $this->getMongoCollection($document);
		$loaded = $collection->findOne(array('id' => $document['id']));
		unset($loaded['queriableProperties']);
		return $loaded;
	}

	/**
	 * load it from mongo
	 *
	 * @param \Foomo\Cache\CacheResource $resource
	 * @param boolean $countHits
	 *
	 * @return \Foomo\Cache\CacheResource
	 */
	public function load(\Foomo\Cache\CacheResource $resource, $countHits = false)
	{
		try {
			$document = \get_object_vars($resource);
			$loaded = $this->loadDocument($document);
			if (!isset($loaded)) {
				return null;
			}
			//map array to object
			$ret = self::mapDocumentToResource($loaded);

			if ($countHits) {
				$loaded['hits']++;
				$collection = $this->getMongoCollection($loaded);
				$collection->save($loaded);
			}
			return $ret;
		} catch (\Exception $e) {
			\trigger_error(__METHOD__ . ' : ' . $e->getMessage());
			return null;
		}
	}

	public static function mapDocumentToResource($document)
	{
		$ret = new \Foomo\Cache\CacheResource();
		foreach ($document as $key => $value) {
			//map values, but do not take the mongo id, unserialize value
			switch ($key) {
				case 'value':
					$ret->$key = \unserialize($value);
					break;
				case '_id':
					break;
				default:
					$ret->$key = $value;
			}
		}
		return $ret;
	}

	public function delete(\Foomo\Cache\CacheResource $resource)
	{
		try {
			$document = \get_object_vars($resource);
			$loaded = $this->loadDocument($document);

			if (isset($loaded)) {
				$collection = $this->getMongoCollection($loaded);
				$success = $collection->remove(array('id' => $loaded['id']));
				return ($success['ok'] == true);
			} else {
				return true;
			}
		} catch (\Exception $e) {
			\trigger_error(__METHOD__ . ' : ' . $e->getMessage(), \E_USER_WARNING);
			return false;
		}
	}

//

	/**
	 * clear all. if resource name provided drops resource table, else remove recreates entire db
	 *
	 * @param string $resourceName , if null all will be deleted.
	 *
	 * @param bool $recreateStructures if true, storage structures, e.g. tables, are re-created during reset
	 *
	 * @param bool $verbose
	 */
	public function reset($resourceName = null, $recreateStructures = true, $verbose = false)
	{
		try {

			if (isset($resourceName)) {
				$collectionName = $this->collectionNameFromResourceName($resourceName);
				$collection = $this->database->$collectionName;
				$this->removeCollection($collection, $recreateStructures, $verbose);
			} else {
				$list = $this->database->listCollections();
				foreach ($list as $collection) {
					$this->removeCollection($collection, $recreateStructures, $verbose);
				}
			}
		} catch (\Exception $e) {
			\trigger_error(__CLASS__ . __METHOD__ . $e->getMessage());
		}
	}

	private function removeCollection($collection, $recreateStructures, $verbose)
	{
		if ($verbose) {
			echo '... removing collection ' . $collection->getName() . \PHP_EOL;
		}
		$collection->drop();
		if ($verbose) {
			echo "........... Done" . \PHP_EOL;
		}
		if ($recreateStructures) {
			// nothing to do for mongo - no db schema!
			if ($verbose) {
				echo "........... recreating structure does not make sense for mongo" . \PHP_EOL;
			}
		}
	}

	/**
	 * finds all resources matching expression
	 *
	 * @param string $resourceName
	 * @param \Foomo\Cache\Persistence\Expr $expr
	 * @param integer $limit
	 * @param integer $offset
	 *
	 * @return MongoPersistorIterator
	 */
	public function query($resourceName, $expr, $limit, $offset)
	{
		$collectionName = self::collectionNameFromResourceName($resourceName);
		$collection = $this->database->$collectionName;
		$condition = \Foomo\Cache\Persistence\Queryable\MongoExpressionCompiler::buildMongoQuery($expr);

		//var_dump($condition,$collection->getName()); exit;
		$cursor = $collection->find($condition);
		if ($limit != 0)
			$cursor->limit($limit);
		if ($offset != 0)
			$cursor->skip($offset);
		return new \Foomo\Cache\Persistence\Queryable\MongoPersistorIterator($cursor, $resourceName);
	}

	/**
	 * get all cached resource names
	 *
	 * @return array of resource names
	 */
	public function getCachedResourceNames()
	{
		return array();
	}

	public function getExpressionInterpretation($resourceName, $expression)
	{
		$condition = MongoExpressionCompiler::buildMongoQuery($expression);
		return $condition['$where'];
	}

	/**
	 * validates storage structure against resource annotation
	 *
	 * @param string $resourceName
	 *
	 * @param bool $verbose do we output to stdout
	 *
	 * @return boolean true if valid
	 */
	public function validateStorageStructure($resourceName, $verbose = false)
	{

		$collectionName = $this->collectionNameFromResourceName($resourceName);
		if (!$this->storageStructureExists($resourceName)) {
			if ($verbose === true)
				echo 'Storage structure for resource ' . $resourceName . ' (collection ' . $collectionName . ') does not exist!' . \PHP_EOL;
			return false;
		} else {
			if ($verbose === true)
				echo 'Storage structure for resource ' . $resourceName . ' (collection ' . $collectionName . ') exists.' . \PHP_EOL;
		}

		$collection = $this->database->$collectionName;
		$loadedDocument = $collection->findOne();
		$loadedResource = self::mapDocumentToResource($loadedDocument);

		if (!isset($loadedDocument)) {
			if ($verbose) {
				echo 'Storage structure (mongo collection) is empty. Consequently structure is valid.' . \PHP_EOL;
			}
			return true;
		}

		$resource = \Foomo\Cache\Proxy::getEmptyResourceFromResourceName($resourceName);

		$ret = true;
		if ($verbose) {
			echo \PHP_EOL;
			echo 'For all resource properties .... check if they are mapped correctly....' . \PHP_EOL;
			echo \PHP_EOL;
		}

		foreach ($resource->getPropertyDefinitions() as $propName => $propertyDefinition) {
			$resType = $propertyDefinition->type;
			if (isset($loadedResource->propertyTypes[$propName])) {
				$loadedType = $loadedResource->propertyTypes[$propName];

				if ($loadedType == $resType) {
					echo 'Property ' . $propName . ' of type' . $resType . ' is mapped correctly in the storage structure' . \PHP_EOL;
				} else {
					if ($verbose) {
						echo '------------------------------------------------------------------------------------------------------------------------------------------------------' . \PHP_EOL;
						echo 'Property ' . $propName . ' of type' . $resType . ' is found in storage structure as ' . $loadedType . ' INCORRECT MAPPING!' . \PHP_EOL;
						echo '------------------------------------------------------------------------------------------------------------------------------------------------------' . \PHP_EOL;
					}
					$ret = false;
				}
			} else {
				if ($verbose)
					echo 'Property ' . $propName . ' not found in storage structure' . \PHP_EOL;
				$ret = false;
			}
		}
		return $ret;
	}

	/**
	 * check if storage structure (table) exists for resource
	 *
	 * @param string $resourceName
	 *
	 * @return bool
	 */
	public function storageStructureExists($resourceName)
	{
		$collectionName = $this->collectionNameFromResourceName($resourceName);
		$collection = $this->database->$collectionName;
		$validation = $collection->validate();
		if ($validation && $validation['ok'] == 1) {
			return true;
		} else {
			return false;
		}
	}

}

