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

use \PDO;

/**
 * A PDO peristor implementation. In its present form only for mysql.
 *
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
class PDOPersistor implements \Foomo\Cache\Persistence\QueryablePersistorInterface {
	/**
	 * Max string size
	 */
	const MAX_VARCHAR_SIZE = 512;
	/**
	 * name of table where resource names are stored since in some cases the resource name can not be
	 * deducted from the table name due to the 64 chars limitation in mySQL
	 */
	const RESOURCE_NAMES_TABLE = '$$$CACHED_RESOURCE_NAMES$$$';

	/**
	 * PDO database handle
	 * @var \PDO
	 */
	public $dbh;
	/*
	 * db type
	 *
	 * @var string
	 */
	public $type = '';
	/**
	 * hostname or ip of server
	 * @var string
	 */
	public $serverName = '127.0.0.1';
	/**
	 * user name to access db server
	 *
	 * @var string
	 */
	public $username = 'root';
	/**
	 * password to access db server
	 *
	 * @var string
	 */
	public $password = '';

	/*
	 * port to access db server
	 *
	 * @var string
	 */
	public $port = '';
	/**
	 *
	 * @var string db name
	 */
	public $databaseName;
	private $createIfNotExists = false;
	private static $typeMapping = array(
		'id' => 'CHAR(32)',
		'resource' => 'MEDIUMBLOB',
		'expirationtime' => 'INT',
		'expirationtimefast' => 'INT',
		'hits' => 'INT',
		'status' => 'TINYINT',
		'type_object' => 'CHAR(32)',
		'type_array' => 'CHAR(32)',
		'type_integer' => 'BIGINT',
		'type_long' => 'BIGINT',
		'type_bool' => 'BOOL',
		'type_double' => 'DOUBLE',
		'type_float' => 'FLOAT',
		'type_string' => 'CHAR(32)', //' VARCHAR(' . self::MAX_VARCHAR_SIZE . ')'
		'creationtime' => 'INT',
		'type_mixed' => 'CHAR(32)');

	public function __construct($persistorConfig, $createIfNotExists = false) {
		$this->parseConfig($persistorConfig, $this->type, $this->serverName, $this->port, $this->databaseName, $this->username, $this->password);
		//create if not exist an connect
		$this->createIfNotExists = $createIfNotExists;
		$this->connect($createIfNotExists);
	}

	public function save(\Foomo\Cache\CacheResource $resource) {
		$success = false;
		try {
			//	    WILL NOT CHECK IF TABLE EXISTS TO SPEED UP THINGS. FALLBACK AFTER EXCEPTION HANDLING
			//		Note: this can somehow cause PDO exception interference
			//	    check if table exists and if not create
			//	    if (!$this->tableExists(self::tableNameFromResourceName($resource->name))) {
			//		$this->createTableForResource($resource);
			//	    }
			//
			// insert or update
			$statement = null;
			$this->dbh->beginTransaction();
			if (!$this->recordExists($resource)) {
				$statement = $this->getInsertStatement($resource);
			} else {
				$statement = $this->getUpdateStatement($resource);
			}
			$statement->execute();
			$this->dbh->commit();
			$success = true;
			return true;
		} catch (\Exception $e) {
			$this->dbh->rollBack();
			// try resolving it first by creating a table. If exception is propagated from here onwards- we have a problem with doctrine
			// hence contain it here
			//$this->dbh = null;
			//$this->connect($this->createIfNotExists);
			if ($this->tableExists(self::tableNameFromResourceName($resource->name)) && $this->recordExists($resource)) {
				$dbResource = $this->load($resource);
				trigger_error(__METHOD__ . 'race condition, when saving a resource ? $resource->debugCreationTime:' . $resource->debugCreationTime . ' $dbResource->debugCreationTime:' . $dbResource->debugCreationTime, E_USER_WARNING);
				if ($dbResource->debugCreationTime > $resource->debugCreationTime) {
					\trigger_error(' a never resource is in the db', \E_USER_WARNING);
				}
				trigger_error(__METHOD__ . ' failed in a transaction ' . $e->getMessage(), \E_USER_WARNING);
				return false;
			} else {
				//trigger_error(__METHOD__ . ' failed to save. is the resource table not there yet?' . $resource->name . $e->getMessage(), \E_USER_WARNING);
				if (!$this->tableExists(self::tableNameFromResourceName($resource->name))) {
					/**
					 * if this throws an exception it will be caught at the manager level
					 */
					//trigger_error(__METHOD__ . ' trying to create the table. table was not there!' . $resource->name . $e->getMessage(), \E_USER_WARNING);
					$this->createTableForResource($resource);
					//call save again after table was created.
					return $this->save($resource);
					return true;
				} else {
					\trigger_error(__METHOD__ . ' : ' . $e->getMessage(), \E_USER_WARNING);
					return false;
				}
			}
		}
	}

	/**
	 * load it from the db
	 *
	 * @param Foomo\Cache\CacheResource $resource
	 * @param boolean $countHits
	 *
	 * @return Foomo\Cache\CacheResource
	 */
	public function load(\Foomo\Cache\CacheResource $resource, $countHits = false) {
		try {
			$id = $resource->id;
			$tableName = self::tableNameFromResourceName($resource->name);
			$statement = "SELECT * FROM " . $tableName . " WHERE id = :id";
			$statement = $this->dbh->prepare($statement);
			$statement->bindParam(':id', $id);
			$statement->execute();
			//there should be just one
			$row = $statement->fetch();
			if (!$row)
				return null;
			$object = self::rowToCacheResource($row, $resource->name);
			if ($countHits) {
				$object->hits++;
				$this->writeBackNumberOfHits($object);
			}
			return $object;
		} catch (\Exception $e) {
			// @todo: disabled this as this always comes after autoloader reset
			#\trigger_error(__CLASS__ . __METHOD__ . ' : ' . $e->getMessage());
			return null;
		}
	}

	public function delete(\Foomo\Cache\CacheResource $resource) {
		try {
			$id = $resource->id;
			$tableName = self::tableNameFromResourceName($resource->name);
			$statement = "DELETE FROM " . $tableName . " WHERE id = :id;";
			$statement = $this->dbh->prepare($statement);
			$statement->bindParam(':id', $id, PDO::PARAM_STR);
			$statement->execute();
			return true;
		} catch (\Exception $e) {
			\trigger_error(__CLASS__ . __METHOD__ . $e->getMessage());
			return false;
		}
	}

	/**
	 * clear all. if resource name provided drops resource table, else remove recreates entire db
	 *
	 * @param string $resourceName, if null all will be deleted.
	 *
	 * @param $recreateStructures if true, storage structures, e.g. tables, are re-created during reset
	 *
	 *
	 */
	public function reset($resourceName = null, $recreateStructures = true) {
		try {
			if (isset($resourceName)) {
				// drop table
				$tableName = self::tableNameFromResourceName($resourceName);
				$this->deleteResourceNameFromStored($resourceName);
				$this->dropTable($tableName);
				if ($recreateStructures)
					$this->createTableForResourceName($resourceName);
			} else {
				// drop database
				$this->dropDatabase($this->databaseName);
				$this->createDatabaseIfNotExists($this->databaseName);
				$this->dbh = null;
				$this->connect($this->createIfNotExists);
				$this->createResourceNamesTableIfNotThere();
				$depsModel = \Foomo\Cache\DependencyModel::getInstance();
				if ($recreateStructures) {
					foreach ($depsModel->getAvailableResources() as $availableResource) {
						echo 'Recreating structure for ' . $availableResource . PHP_EOL;
						$this->createTableForResourceName($availableResource);
						echo '.................. done.' . PHP_EOL;
						@\ob_flush();
						\flush();
					}
				}
			}
		} catch (\Exception $e) {
			\trigger_error(__CLASS__ . __METHOD__ . $e->getMessage());
		}
	}

	/**
	 * recreates the table for storing the resource
	 *
	 * @param string $resourceName
	 */
//	public function setUp($resourceName) {
//		try {
//			echo 'Creating structure for ' . $resourceName . \PHP_EOL;
//			$this->dropTable($this->tableNameFromResourceName($resourceName));
//			$this->createTableForResource(\Foomo\Cache\Proxy::getEmptyResourceFromResourceName($resourceName));
//			echo '      Done.' . \PHP_EOL;
//		} catch (\Exception $e) {
//			\trigger_error(__CLASS__ . __METHOD__ . ' : ' . $e->getMessage());
//		}
//	}

	private function createTableForResourceName($resourceName) {
		$emptyResource = \Foomo\Cache\Proxy::getEmptyResourceFromResourceName($resourceName);
		$this->createTableForResource($emptyResource);
	}

	/**
	 * returns an array of resource ids of all resources
	 *
	 * @param string $resourceName if null list all, else only resources matching name
	 */
	public function getListOfCachedResources($resourceName = null) {
		try {
			$ids = array();
			if ($resourceName) {
				$expr = \Foomo\Cache\Persistence\Expr::idNe('this can never be an id');
				$iterator = $this->query($resourceName, $expr, 0, 0);
				foreach ($iterator as $resource) {
					$ids[] = $resource->id;
				}
				return $ids;
			} else {
				try {
					return $this->getIdsFromAllTables();
				} catch (\Exception $e) {
					return array();
					\trigger_error(__CLASS__ . __METHOD__ . $e->getMessage());
				}
			}
		} catch (\Exception $e) {
			\trigger_error(__CLASS__ . __METHOD__ . $e->getMessage());
			return array();
		}
	}

	/**
	 *  get ids of all cached resources
	 *
	 */
	private function getIdsFromAllTables() {
		try {
			$tables = $this->getAvailableTables();
			$ids = array();
			foreach ($tables as $table) {
				$ids = \array_merge($ids, $this->getIdsOfResourcesInTable($table));
			}
			return $ids;
		} catch (\Exception $e) {
			return array();
		}
	}

	private function getIdsOfResourcesInTable($tableName) {
		try {
			$ids = array();
			$statement = "SELECT * FROM " . $tableName . ';';
			$rows = $this->dbh->query($statement)->fetchAll();
			foreach ($rows as $row) {
				$ids[] = $row['id'];
			}
			return $ids;
		} catch (\Exception $e) {
			return array();
		}
	}

	/**
	 * finds all resources matching expression
	 *
	 * @param string $resourceName
	 *
	 * @param \Foomo\Cache\Persistence\Expr $expr
	 *
	 * @param integer $limit
	 *
	 * @param integer $offset
	 *
	 * @return PDOPersistorIterator
	 */
	public function query($resourceName, $expr, $limit, $offset) {
		$tableName = self::tableNameFromResourceName($resourceName);

		//handle case where table was created but settup not called (no table yet)
		if (!$this->tableExists($tableName)) {
			//hence no entries
			return $iterator = new PDOPersistorIterator(null, $resourceName);
		} else { //regulat query
			$parameterTypes = \Foomo\Cache\Proxy::getEmptyResourceFromResourceName($resourceName)->propertyTypes;
			//if we could not find the types any search will result empty, hence return empty iterator
			if (\is_null($parameterTypes)) {
				trigger_error('parameterTypes were null', E_USER_ERROR);
			} else {
				$parameterStack = array();
				$sql = \Foomo\Cache\Persistence\Queryable\PDOExpressionCompiler::buildSQLQuery($expr, $parameterStack, $tableName, $parameterTypes);
				if ($limit != 0)
					$sql .= ' LIMIT ' . $limit;
				if ($offset != 0)
					$sql .= ' OFFSET ' . $offset;
				$sql .= ";";

				$cursor = 0;
				$statement = $this->dbh->prepare($sql);
				foreach ($parameterStack as $parameter) {
					$cursor++;
					$statement->bindParam($cursor, $parameter[1], $parameter[2]);
				}
				$statement->execute();
				$iterator = new PDOPersistorIterator($statement, $resourceName);
				return $iterator;
			}
		}
	}

	/**
	 * connect to db. attempt to create if not exists. sets the $dbh property
	 * connections are persistent to speed up things: PDO::ATTR_PERSISTENT => true
	 * @param bool $createIfNotExists
	 * @param integer $attempt if we can not connect we call ourselves another time, attempt is increased by 1.
	 * @return boolean
	 */
	protected function connect($createIfNotExists = true, $attempt = 0) {

		$max_retries = 10;

		try {
			// $dsn = 'mysql:dbname=' . $this->databaseName . ";host=" . $this->serverName . ":" . $this->port;
			// by schmidk
			// Important API change: port needs to be appended separately. See http://www.php.net/manual/en/pdo.connections.php
			$dsn = 'mysql:dbname=' . $this->databaseName . ";host=" . $this->serverName;
			if (!empty($this->port)) {
				$dsn .= ";port=" . $this->port;
			}
			if ($createIfNotExists) {
				$this->createDatabaseIfNotExists($this->databaseName);
			}
			$this->dbh = @new \PDO($dsn, $this->username, $this->password, array(
						PDO::ATTR_PERSISTENT => true,
						PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
					));
			if ($attempt > 0)
				\trigger_error(__METHOD__ . ' PDO connected at attempt: ' . $attempt);
			$this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

			$db_info = $this->dbh->getAttribute(PDO::ATTR_SERVER_INFO); //  erkennung von toten persistenten verbindungen ...

			if ($db_info == "MySQL server has gone away") {
				$this->dbh = null;
				$this->dbh = @new \PDO($dsn, $this->username, $this->password);
				\trigger_error(__METHOD__ . ' using a non-persistent PDO connection');
			}
			//$this->dbh->exec('set character set utf8;');
			return true;
		} catch (\Exception $e) {
			$this->dbh = null;
			if ($attempt >= $max_retries) {
				\trigger_error(__CLASS__ . __METHOD__ . $e->getMessage() . ' after trying ' . $max_retries . ' times.');
				return false;
			}
			//we create the dB if not there and try connecting again
			if ($attempt == 0)
				$this->createDatabaseIfNotExists($this->databaseName);
			// sleep if not first retry
			if ($attempt > 1) {
				\usleep($attempt * 100000);
			}
			return $this->connect($this->createIfNotExists, $attempt + 1);
		}
	}

	/**
	 * create db if not there
	 * uses mysql api as PDO does not allow to create db before establishing connection with mysql
	 * @param string $databaseName
	 */
	protected function createDatabaseIfNotExists($databaseName) {
		try {
			mysql_connect($this->serverName . ":" . $this->port, $this->username, $this->password);
			$databaseName = \mysql_real_escape_string($databaseName);
			$query = "CREATE DATABASE IF NOT EXISTS " . $databaseName . ";";
			if (mysql_query($query)) {
				mysql_select_db($databaseName);
			} else {
				\trigger_error(__CLASS__ . __METHOD__ . ' : ' . mysql_error());
			}
			\mysql_close();
		} catch (\Exception $e) {
			\trigger_error(__CLASS__ . __METHOD__ . $e->getMessage());
		}
	}

	protected function dropDatabase($databaseName) {
		try {
			mysql_connect($this->serverName . ":" . $this->port, $this->username, $this->password);
			$databaseName = \mysql_real_escape_string($databaseName);
			$query = "DROP DATABASE " . $databaseName . ";";
			if (mysql_query($query)) {
				//\mysql_select_db($databaseName);
			} else {
				\trigger_error(__CLASS__ . __METHOD__ . ' : ' . mysql_error());
			}
			\mysql_close();
		} catch (\Exception $e) {
			\trigger_error(__CLASS__ . __METHOD__ . $e->getMessage());
		}
	}

	/**
	 * Create a table to store resource with parameters
	 * @throws Exception if could not create table
	 */
	protected function createTableForResource(\Foomo\Cache\CacheResource $resource) {

		$tableName = self::tableNameFromResourceName($resource->name);

		try {
			$statement = 'CREATE TABLE IF NOT EXISTS ' . $tableName .
					' (id ' . self::$typeMapping['id'] . ' NOT NULL UNIQUE, resource ' . self::$typeMapping['resource'] .
					', status ' . self::$typeMapping['status'] .
					', creationtime ' . self::$typeMapping['creationtime'] .
					', expirationtime ' . self::$typeMapping['expirationtime'] .
					', expirationtimefast ' . self::$typeMapping['expirationtimefast'] .
					', hits ' . self::$typeMapping['hits'] .
					', UNIQUE (id)) ENGINE = InnoDB, DEFAULT CHARACTER SET utf8, DEFAULT COLLATE utf8_general_ci;'; //MYISAM

			$this->dbh->exec($statement);

			//add index on id
			$this->dbh->exec("ALTER TABLE " . $tableName . " ADD INDEX(id);");

			//add the columns for the properties
			/* @var $propertyDefintion \Foomo\Cache\CacheResourcePropertyDefinition */
			foreach ($resource->getPropertyDefinitions() as $parameterName => $propertyDefintion) {
				$columnName = $this->paramNameToColName($parameterName);
				$query = 'ALTER TABLE ' . $tableName . ' ADD ' . $columnName . ' ';
				$typeMappingName = 'type_' . $propertyDefintion->type;

				if (isset(self::$typeMapping[$typeMappingName])) {
					$query .= self::$typeMapping[$typeMappingName];
				} else {
					if ($propertyDefintion->typeIsArray()) {
						$query .= self::$typeMapping['type_array'];
					} else {
						$query .= self::$typeMapping['type_object'];
					}
				}

				//$query .= " ADD INDEX " . $columnName . "_index (" . $columnName .");";
				$this->dbh->exec($query);

				//add index on property column
				//$this->dbh->exec("ALTER TABLE " . $tableName . " ADD INDEX(".$columnName.");");
			}
			//if successful add table to existing tables
			$this->storeResourceName($resource->name);
		} catch (\Exception $e) {
			\trigger_error(__CLASS__ . __METHOD__ . ' : ' . $e->getMessage());
			$this->dropTable($tableName);
			throw $e;
		}
	}

	/**
	 * creates a table to store resource names if conversion of
	 *
	 * tablenames to resource names is required
	 */
	private function createResourceNamesTableIfNotThere() {
		try {
			$tableName = PDOPersistor::RESOURCE_NAMES_TABLE;
			$statement = "CREATE TABLE IF NOT EXISTS " . $tableName . " (tableName VARCHAR(64) NOT NULL UNIQUE, resourceName VARCHAR(1024), UNIQUE(tableName)) ENGINE = MYISAM;";
			$this->dbh->exec($statement);
		} catch (\Exception $e) {
			\trigger_error(__CLASS__ . __METHOD__ . $e->getMessage());
		}
	}

	private function storeResourceName($resourceName) {
		$tableName = self::tableNameFromResourceName($resourceName);

		if ($this->isTableNameStored($tableName))
			return;

		$table = PDOPersistor::RESOURCE_NAMES_TABLE;
		try {
			$statement = "INSERT INTO " . $table . " VALUES (:tableName, :resourceName);";

			$statement = $this->dbh->prepare($statement);
			$statement->bindParam(':tableName', $tableName);
			$statement->bindParam(':resourceName', $resourceName);
			$statement->execute();
		} catch (\Exception $e) {
			\trigger_error(__CLASS__ . __METHOD__ . $e->getMessage());
			try {
				$this->createResourceNamesTableIfNotThere();
				$this->storeResourceName($resourceName);
			} catch (\Exception $e1) {
				\trigger_error(__CLASS__ . __METHOD__ . $e1->getMessage());
			}
		}
	}

	private function isTableNameStored($tableName) {
		$table = PDOPersistor::RESOURCE_NAMES_TABLE;
		try {
			$statement = "SELECT * FROM " . $table . " WHERE tableName = :tableName;";

			$statement = $this->dbh->prepare($statement);
			$statement->bindParam(':tableName', $tableName);
			$statement->execute();
			$res = $statement->fetchAll();
			if (count($res) > 0)
				return true;
			else
				return false;
		} catch (\Exception $e) {
			return false;
		}
	}

	private static function getResourceNameFromStored($tableName) {
		try {
			$resourceTable = PDOPersistor::RESOURCE_NAMES_TABLE;
			$statement = "SELECT resourceName FROM " . $resourceTable . " WHERE tableName = :tableName;";
			$statement = $this->dbh->prepare($statement);
			$statement->bindParam(':tableName', $tableName);
			$result = $this->dbh->query($statement);
			if ($result->rowCount() == 0)
				return null;
			else
				return $result[0];
		} catch (\Exception $e) {
			\trigger_error(__CLASS__ . __METHOD__ . $e->getMessage());
			return null;
		}
	}

	private function deleteResourceNameFromStored($resourceName) {
		try {
			//$tableName = self::tableNameFromResourceName($resourceName);
			$resourceTable = PDOPersistor::RESOURCE_NAMES_TABLE;
			$statement = "DELETE FROM " . $resourceTable . " WHERE tableName = :resourceName;";
			$statement = $this->dbh->prepare($statement);
			$statement->bindParam(':resourceName', $resourceName);
			$statement->execute();
		} catch (\Exception $e) {
			\trigger_error(__CLASS__ . __METHOD__ . $e->getMessage(), \E_USER_WARNING);
		}
	}

	/**
	 * get all cached resource names
	 *
	 * @return array of resource names
	 */
	public function getCachedResourceNames() {
		$statement = "SELECT resourceName FROM " . self::RESOURCE_NAMES_TABLE . ";";
		$statement = $this->dbh->prepare($statement);
		$statement->execute();
		$resultArray = $statement->fetchAll();
		return $resultArray;
	}

	/**
	 * all cached resources with name
	 *
	 * @param string $resourceName
	 *
	 * @return <type>
	 */
	public function getAllCachedResources($resourceName) {
		$expr = \Foomo\Cache\Persistence\Expr::idNe('we want all - this is not an id');
		$resourceIterator = $this->query($resourceName, $expr);
		return $resourceIterator;
	}

	/**
	 * cache the table names of existing tables
	 */
	private function getAvailableTables() {
		try {
			$statement = $this->dbh->query("SHOW TABLES;");
			$availableTables = array();
			while ($row = $statement->fetch(PDO::FETCH_NUM)) {
				//var_dump($row[0]);
				$availableTables[] = $row[0];
			}
			return $availableTables;
		} catch (\Exception $e) {
			\trigger_error(__CLASS__ . __METHOD__ . $e->getMessage());
			return array();
		}
	}

	public static function paramNameToColName($paramName) {
		return 'param_' . $paramName;
	}

	private function colNameToParameterName($colName) {
		return \substr($colName, 6);
	}

	public static function tableNameFromResourceName($resourceName) {
		$resourceName = \str_replace("\\", "$", $resourceName);
		if (\strpos($resourceName, "->"))
			$index = \stripos($resourceName, "->") + 2;
		else
			$index = \stripos($resourceName, "::") + 2;
		if ($index < 0)
			$index = 0;
		$name = \substr($resourceName, $index);
		$len = \strlen($name);
		if ($len > 32) {
			$name = \substr($name, $len - 32);
		}
		$name .= \md5($resourceName);
		return $name;
	}

	public static function resourceNameFromTableName($tableName) {
		return self::getResourceNameFromStored($tableName);
	}

	protected function dropTable($tableName) {
		if ($this->tableExists($tableName) === false)
			return;

		try {
			// drop the resource table
			$statement = "DROP TABLE " . $tableName . ";";
			$statement = $this->dbh->prepare($statement);
			$statement->execute();
			// drop the reference

			$resourceNamesTable = self::RESOURCE_NAMES_TABLE;
			$statement = "DELETE FROM " . $resourceNamesTable . " where tableName = :table;";
			$statement = $this->dbh->prepare($statement);
			$statement->bindParam(':table', $tableName);
			$statement->execute();
		} catch (\Exception $e) {
			\trigger_error(__CLASS__ . __METHOD__ . $e->getMessage());
		}
	}

	public function tableExists($tableName) {
		$statement = 'SELECT 1 FROM ' . $tableName . ' limit 1;';
		try {
			$statement = $this->dbh->prepare($statement);
			$statement->execute();
			return true;
		} catch (\Exception $e) {
			return false;
		}
	}

	/**
	 * 	Check if record with resource exists
	 * @param \Foomo\Cache\CacheResource $resource
	 * @return <type>
	 * @throws \Exception (PDOException) if tabel not exists or other error
	 */
	protected function recordExists(\Foomo\Cache\CacheResource $resource) {
		try {
			$id = $resource->id;
			$tableName = self::tableNameFromResourceName($resource->name);
			$statement = "SELECT * FROM " . $tableName . " WHERE id = :id;";
			$statement = $this->dbh->prepare($statement);
			$statement->bindParam(':id', $id, PDO::PARAM_STR);
			$statement->execute();
			$row = $statement->fetch();
			if ($row)
				return true;
			else
				return false;
		} catch (\Exception $e) {
			// @todo: disabled this as this always comes after autoloader reset
			#\trigger_error(__CLASS__ . __METHOD__ . $e->getMessage());
			return false;
		}
	}

	/**
	 * compute a fingerpring of an object. for object that implement __toString returns md5 of its return value
	 *
	 * otherwise returns md5 of serialized object, which may be  slower for large objects
	 *
	 * @param mixed $object
	 *
	 * @return string
	 */
	private static function getObjectFingerprint($object) {
		switch (true) {
			case is_object($object):
				if (\method_exists($object, '__toString')) {
					$ret = $object->__toString();
				} else {
					$ret = \serialize($object);
				}
				break;
			case is_array($object):
				$ret = \serialize($object);
				break;
			case is_string($object):
				$ret = $object;
				break;
			case is_null($object):
				$ret = '';
				break;
			default:
				trigger_error('that was unexpected ' . \var_export($object, true), \E_USER_ERROR);
		}
		if (\strlen($ret) > 32) {
			return \md5($ret);
		} else {
			return $ret;
		}
	}

	protected function getInsertStatement(\Foomo\Cache\CacheResource $resource) {
		// put properties into string
		$tableName = self::tableNameFromResourceName($resource->name);
		$statement = "INSERT INTO " .
				$tableName .
				" VALUES (:id, :resource, :status, :creationTime, :expirationTime, :expirationTimeFast, :hits";

		$propertyNames = \array_keys($resource->properties);

		foreach ($propertyNames as $pName) {
			$pName = self::paramNameToColname($pName);
			$statement .= ", :" . $pName;
		}

		$statement .= ");";
		$statement = $this->dbh->prepare($statement);
		//set parameters
		$statement->bindParam(':id', $resource->id, PDO::PARAM_STR);
		$serializedResource = \serialize($resource);
		$statement->bindParam(':resource', $serializedResource, PDO::PARAM_LOB);
		$statement->bindParam(':status', $resource->status, PDO::PARAM_INT);
		$statement->bindParam(':creationTime', $resource->creationTime, PDO::PARAM_INT);
		$statement->bindParam(':expirationTime', $resource->expirationTime, PDO::PARAM_INT);
		$statement->bindParam(':expirationTimeFast', $resource->expirationTimeFast, PDO::PARAM_INT);
		$statement->bindParam(':hits', $resource->hits, PDO::PARAM_INT);
		$this->bindPropertiesOnStatement($statement, $resource);
		return $statement;
	}

	protected function getUpdateStatement(\Foomo\Cache\CacheResource $resource) {
		// put properties into string
		$tableName = self::tableNameFromResourceName($resource->name);
		$statement = "UPDATE " . $tableName .
				" SET " . "resource = :resource, " .
				"status=  :status, " .
				"creationTime = :creationTime," .
				"expirationtime = :expirationTime," .
				"expirationtimeFast = :expirationTimeFast," .
				"hits = :hits";

		// add the properties
		$propertyNames = \array_keys($resource->properties);
		foreach ($propertyNames as $propertyName) {
			$propertyName = self::paramNameToColname($propertyName);
			$statement .= ', ' . $propertyName . ' =:' . $propertyName;
		}
		$statement .= " WHERE id = :id;";
		$statement = $this->dbh->prepare($statement);

		//set parameters

		$statement->bindParam(':id', $resource->id, PDO::PARAM_STR);
		$serializedResource = \serialize($resource);
		$statement->bindParam(':resource', $serializedResource, PDO::PARAM_LOB);
		$statement->bindParam(':status', $resource->status, PDO::PARAM_INT);
		$statement->bindParam(':creationTime', $resource->creationTime, PDO::PARAM_INT);
		$statement->bindParam(':expirationTime', $resource->expirationTime, PDO::PARAM_INT);
		$statement->bindParam(':expirationTimeFast', $resource->expirationTimeFast, PDO::PARAM_INT);
		$statement->bindParam(':hits', $resource->hits, PDO::PARAM_INT);
		// bind parameter values
		$this->bindPropertiesOnStatement($statement, $resource);
		return $statement;
	}

	private function bindPropertiesOnStatement($statement, $resource) {
		foreach ($resource->properties as $propertyName => $propertyValue) {
			$isMixed = false;
			if ($resource->propertyTypes[$propertyName] == 'mixed') {
				$isMixed = true;
			}
			$statement = $this->bindParameterForProperty($statement, ":" . self::paramNameToColname($propertyName), $propertyValue, $isMixed);
		}
	}

	private function bindParameterForProperty($statement, $propertyName, $propertyValue, $isMixed) {

		if ($isMixed === true) {
			//handle it as an object
			$propertyValue = $this->getObjectFingerprint($propertyValue);
			$statement->bindParam($propertyName, $propertyValue, PDO::PARAM_STR);
		} else {
			//$propertyName = self::paramNameToColname($propertyName);
			if (\is_object($propertyValue)) {
				$propertyValue = $this->getObjectFingerprint($propertyValue);
				$statement->bindParam($propertyName, $propertyValue, PDO::PARAM_STR);
			} else if (\is_array($propertyValue)) {
				$propertyValue = $this->getObjectFingerprint($propertyValue);
				$statement->bindParam($propertyName, $propertyValue, PDO::PARAM_STR);
			} else if (\is_bool($propertyValue)) {
				$statement->bindParam($propertyName, $propertyValue, PDO::PARAM_BOOL);
			} else if (\is_double($propertyValue)) {
				// covers float too
				$statement->bindParam($propertyName, $propertyValue);
			} else if (\is_int($propertyValue)) {
				$statement->bindParam($propertyName, $propertyValue, PDO::PARAM_INT);
			} else if (\is_long($propertyValue)) {
				$statement->bindParam($propertyName, $propertyValue, PDO::PARAM_INT);
			} else if (\is_string($propertyValue)) {
				$propertyValue = $this->getObjectFingerprint($propertyValue);
				$statement->bindParam($propertyName, $propertyValue, PDO::PARAM_STR);
			} else if (!isset($propertyValue)) {
				//null treated as object or string
				$propertyValue = $this->getObjectFingerprint($propertyValue);
				$statement->bindParam($propertyName, $propertyValue, PDO::PARAM_STR);
			} else {//default is object
				$propertyValue = $this->getObjectFingerprint($propertyValue);
				$statement->bindParam($propertyName, $propertyValue, PDO::PARAM_STR);
			}
		}
		return $statement;
	}

	public static function getStorablePropertyRepresentation($propertyValue, $isMixed = false) {
		if ($isMixed) {
			return self::getObjectFingerprint($propertyValue);
		} else {
			if (\is_object($propertyValue)) {
				return self::getObjectFingerprint($propertyValue);
			} else if (\is_array($propertyValue)) {
				return self::getObjectFingerprint($propertyValue);
			} else if (\is_bool($propertyValue)) {
				return $propertyValue;
			} else if (\is_float($propertyValue)) {
				return $propertyValue;
			} else if (\is_double($propertyValue)) {
				return $propertyValue;
			} else if (\is_int($propertyValue)) {
				return $propertyValue;
			} else if (\is_long($propertyValue)) {
				return $propertyValue;
			} else if (\is_string($propertyValue)) {
				return self::getObjectFingerprint($propertyValue);
			} else if (!isset($propertyValue)) {
				//null treated as object or string
				return self::getObjectFingerprint($propertyValue);
			} else {//default is object
				return self::getObjectFingerprint($propertyValue);
			}
		}
	}

	/**
	 * format properties as part of SQL query
	 * @param array $parameters
	 */
	public static function getPropertiesQueryString($properties) {
		$str = "";
		foreach ($properties as $n => $v) {
			$n = self::paramNameToColName($n);

			if ($str == "") {
				$str .= $n . ' = :' . $n;
			} else {
				$str .= ' AND ' . $n . ' = :' . $n;
			}
		}
		return $str;
	}

	public static function rowToCacheResource($row, $resourceName) {
		try {
			$resource = \unserialize($row['resource']);
			$resource->hits = $row['hits'];
			return $resource;
		} catch (\Exception $e) {
			trigger_error(__CLASS__ . __METHOD__ . " : " . "Could not unserialize resource");
			throw new \Exception('could not unserialize resource');
		}
	}

	private function writeBackNumberOfHits($resource) {
		try {
			$tableName = self::tableNameFromResourceName($resource->name);
			$statement = "UPDATE " . $tableName . " SET hits = :hits WHERE id = :id;";
			$statement = $this->dbh->prepare($statement);
			$statement->bindParam(':id', $resource->id, PDO::PARAM_STR);
			$statement->bindParam(':hits', $resource->hits, PDO::PARAM_INT);
			$this->dbh->exec($statement);
		} catch (\Exception $e) {
			\trigger_error(__CLASS__ . __METHOD__ . $e->getMessage());
		}
	}

	/**
	 * get a db connection
	 *
	 * @return \Doctrine\DBAL\Connection
	 */
	private function parseConfig($configStr, &$type, &$serverName, &$port, &$dbName, &$username, &$password) {
//parse the data
		$parsed = \parse_url($configStr);
		$type = $parsed['scheme'];
		if ($parsed['scheme'] != 'mysql')
			throw new \Exception('Specified database type ' . $type . ' not supported.');
		$username = isset($parsed['user']) ? $parsed['user'] : '';
		$password = isset($parsed['pass']) ? $parsed['pass'] : '';
		$port = isset($parsed['port']) ? $parsed['port'] : '3306';
		$dbName = \substr($parsed['path'], 1);


		$serverName = $parsed['host'];
	}

	public function getExpressionInterpretation($resourceName, $expression) {
		$tableName = self::tableNameFromResourceName($resourceName);
		$parameterStack = array();
		
		$parameterTypes = \Foomo\Cache\Proxy::getEmptyResourceFromResourceName($resourceName)->propertyTypes;
		//if we could not find the types any search will result empty, hence return empty iterator
		if (\is_null($parameterTypes)) {
			trigger_error('parameterTypes were null', E_USER_ERROR);
		} else {

			$sql = \Foomo\Cache\Persistence\Queryable\PDOExpressionCompiler::buildSQLQuery($expression, $parameterStack, $tableName, $parameterTypes);
//			if ($limit != 0)
//				$sql .= ' LIMIT ' . $limit;
//			if ($offset != 0)
//				$sql .= ' OFFSET ' . $offset;
			$sql .= ";";

			//change the ? with data from parameterStack
			$explodedSQL = \explode('?', $sql);
			$sql = '';
			$i = -1;
			foreach ($explodedSQL as $part) {
				$i++;
				if (isset($parameterStack[$i][1])) {
					$sql .= ' ' . \trim($part) . ' ' . \trim($parameterStack[$i][1]);
				} else {
					$sql .= \trim($part);
				}
			}
			$sql = \trim($sql);
			return \trim($sql);
		}
	}

	/**
	 * check if storage structure (table) exists for resource
	 *
	 * @param string $resourceName
	 *
	 * @return bool
	 */
	public function storageStructureExists($resourceName) {
		$tableName = $this->tableNameFromResourceName($resourceName);
		return $this->tableExists($tableName);
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
	public function validateStorageStructure($resourceName, $verbose = false) {
		$tableName = self::tableNameFromResourceName($resourceName);
		if (!$this->tableExists($tableName)) {
			if ($verbose === true)
				echo 'Storage structure for resource ' . $resourceName . ' (table ' . $tableName . ') does not exist!' . \PHP_EOL;
			return false;
		} else {
			if ($verbose === true)
				echo 'Storage structure for resource ' . $resourceName . ' (table ' . $tableName . ') exists.' . \PHP_EOL;
		}

		$tableInformation = $this->retrieveTableInformation($tableName);
		$resource = \Foomo\Cache\Proxy::getEmptyResourceFromResourceName($resourceName);

		$ret = true;
		if ($verbose) {
			echo \PHP_EOL;
			echo 'For all resource properties .... check if they are mapped correctly....' . \PHP_EOL;
			echo \PHP_EOL;
		}
		// first through the property definitions
		foreach ($resource->getPropertyDefinitions() as $parameterName => $propertyDefintion) {
			$annotationParameter = \strtolower($parameterName);
			$typeMappingName = 'type_' . $propertyDefintion->type;

			if (isset(self::$typeMapping[$typeMappingName])) {
				$annotationParameterType = \strtoupper(self::$typeMapping[$typeMappingName]);
			} else {
				if ($propertyDefintion->typeIsArray()) {
					$annotationParameterType = \strtoupper(self::$typeMapping['type_array']);
				} else {
					$annotationParameterType = \strtoupper(self::$typeMapping['type_object']);
				}
			}

			$annotationParameterType = self::removeTypeBraces($annotationParameterType);

			$dbColumnType = $this->getDBColumnType($this->paramNameToColName($annotationParameter), $tableName);



			if ($dbColumnType == $annotationParameterType) {
				if ($verbose)
					echo 'Comparing annotation of param ' . $annotationParameter . '  type ' . $propertyDefintion->type . '/ to be mapped into ' . $annotationParameterType . ' is mapped correctly to ' . $dbColumnType . \PHP_EOL;
			}else {
				if ($verbose) {
					echo '------------------------------------------------------------------------------------------------------------------------------------------------------' . \PHP_EOL;
					echo 'Comparing annotation of param ' . $annotationParameter . '  type ' . $propertyDefintion->type . '/ to be mapped into ' . $annotationParameterType . ' is INCORRECTLY MAPPED to ' . $dbColumnType . \PHP_EOL;
					echo '------------------------------------------------------------------------------------------------------------------------------------------------------' . \PHP_EOL;
				}
				$ret = false;
			}
		}


		// second another round through all the existing db columns
		if ($verbose) {
			echo \PHP_EOL;
			echo 'For all storage fields (columns) .... check if their type matches annotation ....' . \PHP_EOL;
			echo \PHP_EOL;
		}

		foreach ($tableInformation as $column) {

			$type = self::removeTypeBraces(\strtoupper($column['Type']));
			$name = \strtolower($column['Field']);
			$column['Field'] = $name;
			$column['Type'] = $type;
			$annotatedTypeMapped = $this->getDBColumnTypeFromAnnotationMapped($name, $resource);
			$annotatedType = $this->getDBColumnTypeFromAnnotation($name, $resource);

			$mismatch = true;
			if (\strtoupper($column['Type']) != $annotatedTypeMapped) {
				$mismatch = true;
				$ret = false;
			} else {
				$mismatch = false;
			}

			if ($mismatch && $verbose) {
				echo '' . \PHP_EOL;
				echo '-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------' . \PHP_EOL;
			}

			if ($verbose) {
				echo ' -> Column ' . $column['Field'] . ' has type ' . $type . '/ Annotation type  ' . '(' . $annotatedType . ')' . ' is mapped to ' . $annotatedTypeMapped . \PHP_EOL;
			}
			if (!$mismatch) {
				if ($verbose)
					echo '................. OK' . \PHP_EOL;
			}
			if ($mismatch === true && $verbose) {
				echo '------------------------------------> STORAGE STRUCTURE TYPE NOT UPTODATE. CACHE STRUCTURE INCONSISTENCY. PLEASE SETUP CACHE RESOURCE FOR RESOURCE: ' . $resourceName . \PHP_EOL;
				echo '' . \PHP_EOL;
			}

			if ($mismatch && $verbose) {
				echo '-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------' . \PHP_EOL;
			}
		}
		return $ret;
	}

	/**
	 * tells what type the annotation expects the db colum to be, i.e. what the annotation maps to
	 *
	 * @param string $propName db column name
	 *
	 * @return string type of column param_xxxxx
	 */
	private function getDBColumnTypeFromAnnotationMapped($columnName, $resource) {
		if (\array_key_exists($columnName, self::$typeMapping)) {
			return self::removeTypeBraces(self::$typeMapping[$columnName]);
		} else {

			/* @var $propertyDefintion \Foomo\Cache\CacheResourcePropertyDefinition */
			foreach ($resource->getPropertyDefinitions() as $parameterName => $propertyDefintion) {

				$parameterName = \strtolower($parameterName);
				$decoratedName = $this->paramNameToColName($parameterName);

				if ($columnName == $decoratedName) {
					$typeMappingName = 'type_' . $propertyDefintion->type;

					if (isset(self::$typeMapping[$typeMappingName])) {
						return self::removeTypeBraces(self::$typeMapping[$typeMappingName]);
					} else {
						if ($propertyDefintion->typeIsArray()) {
							return self::removeTypeBraces(self::$typeMapping['type_array']);
						} else {
							return self::removeTypeBraces(self::$typeMapping['type_object']);
						}
					}
				}
			}
		}
	}

	/**
	 * tells what type the annotation expects the db colum to be. Annotation not mapped to db type!
	 *
	 * @param string $propName db column name
	 *
	 * @return string type of column param_xxxxx
	 */
	private function getDBColumnTypeFromAnnotation($columnName, $resource) {
		switch ($columnName) {
			case 'id':
				return 'string';
				break;
			case 'hits':
				return 'integer';
				break;
			case 'status':
				return 'integer';
				break;
			case 'creationtime':
				return 'integer';
				break;
			case 'expirationtime':
				return 'integer';
				break;
			case 'expirationtimefast':
				return 'integer';
				break;
			case 'resource':
				return '\Foomo\Cache\CacheResource';
				break;
			default:

				/* @var $propertyDefintion \Foomo\Cache\CacheResourcePropertyDefinition */
				foreach ($resource->getPropertyDefinitions() as $parameterName => $propertyDefintion) {

					$parameterName = \strtolower($parameterName);
					$decoratedName = $this->paramNameToColName($parameterName);

					if ($columnName == $decoratedName) {
						return $propertyDefintion->type;
					}
				}
				break;
		}
	}

	private static function removeTypeBraces($type) {
		$pos = \strpos($type, '(');
		if ($pos !== false) {
			return \substr($type, 0, $pos);
		} else {
			return $type;
		}
	}

	private function getDBColumnType($columnName, $tableName) {
		$tableInformation = $this->retrieveTableInformation($tableName);
		foreach ($tableInformation as $column) {

			$type = self::removeTypeBraces(\strtoupper($column['Type']));
			$name = \strtolower($column['Field']);

			if ($name == $columnName) {
				return $type;
			}
		}return null;
	}

	private function retrieveTableInformation($table) {
		// code is currently specific to mysql....
		$sql = 'SHOW columns from ' . $table . ';';
		$statement = $this->dbh->query($sql);
		$results = array();

		foreach ($statement as $row) {
			$type = \strtoupper($row['Type']);
			$row['Type'] = $type;
			$name = \strtolower($row['Field']);
			$row['Field'] = $name;
			$results[] = $row;
		}
		return $results;
	}

}
