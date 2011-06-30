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
 * copiles an SQL from a generic expression Foomo\Cache\Persistence\Expr
 * 
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
class PDOExpressionCompiler {

	public static function buildSQLQuery(\Foomo\Cache\Persistence\Expr $expr, &$parameterStack, $tableName, $paramTypes)
	{

		$callStack = $expr->value;
		$sql = "SELECT * FROM " . $tableName;
		$method = $callStack[0];
		$parameters = $callStack[1];

		$value = self::handleCall($method, $parameters, $parameterStack, 0, $paramTypes);
		if (!empty($value))
			$sql .= " WHERE " . $value;
		return $sql;
	}

	private static function handleCall($method, $parameters, &$parameterStack, $level, $paramTypes)
	{
		$subStatement = "";
		switch ($method) {
			case 'Foomo\Cache\Persistence\Expr::groupAnd':
				foreach ($parameters as $parameter) {
					$mtd = null;
					$params = null;
					if ($parameter instanceof \Foomo\Cache\Persistence\Expr) {
						$mtd = $parameter->value[0];
						$params = $parameter->value[1];
					} else {

						$mtd = $parameter[0];
						$params = $parameter[1];
					}
					if (empty($subStatement)) {
						$subStatement = self::handleCall($mtd, $params, $parameterStack, $level + 1, $paramTypes);
					} else {
						$subStatement .= ' AND ' . self::handleCall($mtd, $params, $parameterStack, $level + 1, $paramTypes);
					}
				}
				$subStatement = "(" . $subStatement . ")";
				break;
			case 'Foomo\Cache\Persistence\Expr::groupOr':
				foreach ($parameters as $parameter) {
					$mtd = null;
					$params = null;
					if ($parameter instanceof \Foomo\Cache\Persistence\Expr) {
						$mtd = $parameter->value[0];
						$params = $parameter->value[1];
					} else {

						$mtd = $parameter[0];
						$params = $parameter[1];
					}

					if (empty($subStatement)) {
						$subStatement = self::handleCall($mtd, $params, $parameterStack, $level + 1, $paramTypes);
					} else {
						$subStatement .= ' OR ' . self::handleCall($mtd, $params, $parameterStack, $level + 1, $paramTypes);
					}
				}
				$subStatement = "(" . $subStatement . ")";
				break;
			case 'Foomo\Cache\Persistence\Expr::propEq':
				$isMixed = self::isTypeMixed($parameters[0], $paramTypes);
				$propName = self::decoratePropName($parameters[0]);
				$propValue = PDOPersistor::getStorablePropertyRepresentation($parameters[1], $isMixed);
				$subStatement = $propName . " = ?";
				$propType = self::getPDOType($propValue, $isMixed);
				$line = array($propName, $propValue, $propType);
				$parameterStack[] = $line;
				break;
			case 'Foomo\Cache\Persistence\Expr::propNe':
				$isMixed = self::isTypeMixed($parameters[0], $paramTypes);
				$propName = self::decoratePropName($parameters[0]);
				$propValue = PDOPersistor::getStorablePropertyRepresentation($parameters[1], $isMixed);
				$subStatement = $propName . " != ?";
				$propType = self::getPDOType($propValue, $isMixed);
				$line = array($propName, $propValue, $propType);
				$parameterStack[] = $line;
				break;
			case 'Foomo\Cache\Persistence\Expr::propsEq':
				$properties = $parameters[0];
				if (count($properties) > 0) {
					foreach ($properties as $name => $value) {
						$isMixed = self::isTypeMixed($name, $paramTypes);
						$name = self::decoratePropName($name);
						if (!empty($subStatement)) {
							$subStatement .= ' AND ';
						}
						$subStatement .= $name . " = ?";
						$value = PDOPersistor::getStorablePropertyRepresentation($value, $isMixed);
						$propType = self::getPDOType($value, $isMixed);
						$line = array($name, $value, $propType);
						$parameterStack[] = $line;
					}
					$subStatement = "(" . $subStatement . ")";
				} else {
					$subStatement = '';
				}
				break;
			case 'Foomo\Cache\Persistence\Expr::statusValid':
				$subStatement = "status = ?";
				$propType = self::getPDOType(\Foomo\Cache\CacheResource::STATUS_VALID);
				$line = array('status', \Foomo\Cache\CacheResource::STATUS_VALID, $propType);
				$parameterStack[] = $line;
				break;
			case 'Foomo\Cache\Persistence\Expr::statusInvalid':
				$subStatement = "status = ?";
				$propType = self::getPDOType(\Foomo\Cache\CacheResource::STATUS_INVALID);
				$line = array('status', \Foomo\Cache\CacheResource::STATUS_INVALID, $propType);
				$parameterStack[] = $line;
				break;
			case 'Foomo\Cache\Persistence\Expr::isExpired':
				$cache = 'queryable';
				if (count($parameters) > 1) {
					$cache = $parameters[1];
				}
				if ($cache != 'fast') {
					$subStatement = "(expirationTime > 0 AND expirationTime < " . \time() . ")";
				} else {
					$subStatement = "(expirationTimeFast > 0 AND expirationTimeFast < " . \time() . ")";
				}
				//$parameterStack[] = \Foomo\Cache\CacheResource::STATUS_INVALID;
				break;
			case 'Foomo\Cache\Persistence\Expr::isNotExpired':
				$cache = 'queryable';
				if (count($parameters) > 1) {
					$cache = $parameters[1];
				}
				if ($cache != 'fast') {
					$subStatement = "(expirationTime = 0 OR expirationTime > " . \time() . ")";
				} else {
					$subStatement = "(expirationTimeFast = 0 OR expirationTimeFast > " . \time() . ")";
				}
				break;
			case 'Foomo\Cache\Persistence\Expr::createdBefore':
				$subStatement = "creationTime < ?";
				$timestamp = $parameters[0];
				$propType = self::getPDOType($timestamp);
				$line = array('creationTime', $timestamp, $propType);
				$parameterStack[] = $line;
				break;
			case 'Foomo\Cache\Persistence\Expr::createdAfter':
				$subStatement = "creationTime > ?";
				$timestamp = $parameters[0];
				$propType = self::getPDOType($timestamp);
				$line = array('creationTime', $timestamp, $propType);
				$parameterStack[] = $line;
				break;
			case 'Foomo\Cache\Persistence\Expr::idEq':
				$subStatement = "id = ?";
				$id = $parameters[0];
				$propType = self::getPDOType($id);
				$line = array('id', $id, $propType);
				$parameterStack[] = $line;
				break;
			case 'Foomo\Cache\Persistence\Expr::idNe':
				$subStatement = "id != ?";
				$id = $parameters[0];
				$propType = self::getPDOType($id);
				$line = array('id', $id, $propType);
				$parameterStack[] = $line;
				break;
			case 'Foomo\Cache\Persistence\Expr::hitsMoreThan':
				$hits = $parameters[0];
				$cache = 'queryable';
				if (count($parameters) > 1) {
					$cache = $parameters[1];
				}
				if ($cache == 'queryable') {
					$subStatement = "hits > ?";
					$propType = self::getPDOType($hits);
					$line = array('hits', $hits, $propType);
					$parameterStack[] = $line;
				} else {
					//handle fast cache here... not supported yet
				}
				break;
			case 'Foomo\Cache\Persistence\Expr::hitsLessThan':
				$hits = $parameters[0];
				$cache = 'queryable';
				if (count($parameters) > 1) {
					$cache = $parameters[1];
				}

				if ($cache == 'queryable') {
					$subStatement = "hits < ?";
					$propType = self::getPDOType($hits);
					$line = array('hits', $hits, $propType);
					$parameterStack[] = $line;
				} else {
					//handle fast cache here... not supported yet
				}
				break;
			case 'Foomo\Cache\Persistence\Expr::expirationBefore':
				$timestamp = $parameters[0];

				$cache = 'queryable';
				if (count($parameters) > 1) {
					$cache = $parameters[1];
				}

				if ($cache == 'queryable') {
					$subStatement = "expirationTime < ?";
				} else {
					$subStatement = "expirationTimeFast < ?";
				}
				$propType = self::getPDOType($timestamp);
				$line = array('timestamp', $timestamp, $propType);
				$parameterStack[] = $line;
				break;
			case 'Foomo\Cache\Persistence\Expr::expirationAfter':
				$timestamp = $parameters[0];
				$cache = 'queryable';
				if (count($parameters) > 1) {
					$cache = $parameters[1];
				}

				if ($cache == 'queryable') {
					$subStatement = "expirationTime > ?";
				} else {
					$subStatement = "expirationTimeFast > ?";
				}
				$propType = self::getPDOType($timestamp);
				$line = array('timestamp', $timestamp, $propType);
				$parameterStack[] = $line;

				break;
			default:
				break;
		}
		return $subStatement;
	}

	private static function decoratePropName($propName)
	{
		return PDOPersistor::paramNameToColName($propName);
	}

	private static function getPDOType($propertyValue, $isMixed = false)
	{
		if ($isMixed) {
			return \PDO::PARAM_STR;
		} else {
			if (\is_object($propertyValue)) {
				return \PDO::PARAM_STR;
			} else if (\is_array($propertyValue)) {
				return \PDO::PARAM_STR;
			} else if (\is_bool($propertyValue)) {
				return \PDO::PARAM_BOOL;
			} else if (\is_float($propertyValue)) {
				return \PDO::PARAM_STR;
			} else if (\is_double($propertyValue)) {
				return \PDO::PARAM_STR;
			} else if (\is_int($propertyValue)) {
				return \PDO::PARAM_INT;
			} else if (\is_long($propertyValue)) {
				return \PDO::PARAM_INT;
			} else if (\is_string($propertyValue)) {
				return \PDO::PARAM_STR;
			} else if (!isset($propertyValue)) {
				return \PDO::PARAM_STR;
			} else {//default is object
				return \PDO::PARAM_STR;
			}
		}
	}

	private static function isTypeMixed($propName, $paramTypes)
	{
		$annotatedType = $paramTypes[$propName];
		$isMixed = false;
		if ($annotatedType == 'mixed')
			$isMixed = true;
		return $isMixed;
	}

}