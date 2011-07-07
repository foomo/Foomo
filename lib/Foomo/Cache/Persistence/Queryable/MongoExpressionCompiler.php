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
 * compiles a mongo conditions array from a generic expression \Foomo\Cache\Persistence\Expr
 * 
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
class MongoExpressionCompiler {

	public static function buildMongoQuery(\Foomo\Cache\Persistence\Expr $expr)
	{
		$callStack = $expr->value;
		$condition = array();
		$method = $callStack[0];
		$parameters = $callStack[1];
		$condition = self::handleCall($condition, $method, $parameters, 0);
		return array('$where' => 'function() {return (' . $condition . ");}");
	}

	private static function handleCall($condition, $method, $parameters, $level)
	{
		$subStatement = array();
		$substatement = '';
		$groupRet = '';
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
					$substatement = self::handleCall($subStatement, $mtd, $params, $level + 1);
					if ($groupRet == '') {
						$groupRet .= '(' . $substatement . ')';
					} else {
						$groupRet .= ' && (' . $substatement . ')';
					}
				}
				return $groupRet;
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
					$substatement = self::handleCall($subStatement, $mtd, $params, $level + 1);
					if ($groupRet == '') {
						$groupRet .= '(' . $substatement . ')';
					} else {
						$groupRet .= ' || (' . $substatement . ')';
					}
				}
				return $groupRet;
			case 'Foomo\Cache\Persistence\Expr::propEq':
				$propName = $parameters[0];
				$propValue = $parameters[1];
				$queryableProp = \Foomo\Cache\Persistence\Queryable\MongoPersistor::getQueryableRepresentation($propValue);

				if (\is_string($queryableProp)) {
					$substatement = 'this.queriableProperties.' . $propName . ' == "' . $queryableProp . '"';
				} else {
					$substatement = 'this.queriableProperties.' . $propName . ' == ' . $queryableProp;
				}
				break;
			case 'Foomo\Cache\Persistence\Expr::propNe':
				$propName = $parameters[0];
				$propValue = $parameters[1];

				$queryableProp = \Foomo\Cache\Persistence\Queryable\MongoPersistor::getQueryableRepresentation($propValue);

				if (\is_string($queryableProp)) {
					$substatement = 'this.queriableProperties.' . $propName . ' != "' . $queryableProp . '"';
				} else {
					$substatement = 'this.queriableProperties.' . $propName . ' != ' . $queryableProp;
				}
				break;
			case 'Foomo\Cache\Persistence\Expr::propsEq':

				$substatement = '(';
				$properties = $parameters[0];
				if (count($properties) > 0) {
					foreach ($properties as $name => $value) {
						$queryableProp = \Foomo\Cache\Persistence\Queryable\MongoPersistor::getQueryableRepresentation($value);
						if (\is_string($queryableProp)) {
							if ($substatement == '(') {
								$substatement .= 'this.queriableProperties.' . $name . ' == "' . $queryableProp . '"';
							} else {
								$substatement .= ' && this.queriableProperties.' . $name . ' == "' . $queryableProp . '"';
							}
						} else {
							if ($substatement == '(') {
								$substatement .= 'this.queriableProperties.' . $name . ' == ' . $queryableProp;
							} else {
								$substatement .= ' && this.queriableProperties.' . $name . ' == ' . $queryableProp;
							}
						}
					}
					$substatement .= ')';
				}

				break;
			case 'Foomo\Cache\Persistence\Expr::statusValid':

				$substatement = 'this.status == ' . \Foomo\Cache\CacheResource::STATUS_VALID;
				break;
			case 'Foomo\Cache\Persistence\Expr::statusInvalid':

				$substatement = 'this.status == ' . \Foomo\Cache\CacheResource::STATUS_INVALID;
				break;
			case 'Foomo\Cache\Persistence\Expr::isExpired':
				$cache = 'queryable';
				if (count($parameters) > 1) {
					$cache = $parameters[1];
				}
				if ($cache != 'fast') {
					$substatement = '(this.expirationTime != 0 && this.expirationTime < ' . \time() . ')';
				} else {
					$substatement = '(this.expirationTimeFast != 0 && this.expirationTimeFast < ' . \time() . ')';
				}
				break;
			case 'Foomo\Cache\Persistence\Expr::isNotExpired':
				$cache = 'queryable';
				if (count($parameters) > 1) {
					$cache = $parameters[1];
				}
				$timestamp = \time();
				if ($cache != 'fast') {
					$js = "function() {return (this.expirationTime == 0 || this.expirationTime > " . $timestamp . ");}";
					$subStatement = array('$where' => $js);
					$substatement = '(this.expirationTime == 0 || this.expirationTime > ' . $timestamp . ')';
				} else {
					$js = "function() {return (this.expirationTimeFast == 0 || this.expirationTimeFast > " . $timestamp . ");}";
					$substatement = '(this.expirationTimeFast == 0 || this.expirationTimeFast > ' . $timestamp . ')';
				}
				break;
			case 'Foomo\Cache\Persistence\Expr::createdBefore':
				$timestamp = $parameters[0];
				$substatement = 'this.creationTime < ' . $timestamp;
				break;
			case 'Foomo\Cache\Persistence\Expr::createdAfter':
				$substatement = 'this.creationTime > ' . $timestamp;
				break;
			case 'Foomo\Cache\Persistence\Expr::idEq':
				$substatement = 'this.id == "' . $parameters[0] . '"';
				break;
			case 'Foomo\Cache\Persistence\Expr::idNe':
				$substatement = 'this.id != "' . $parameters[0] . '"';
				break;
			case 'Foomo\Cache\Persistence\Expr::hitsMoreThan':
				$hits = $parameters[0];
				$cache = 'queryable';
				if (count($parameters) > 1) {
					$cache = $parameters[1];
				}
				if ($cache == 'queryable') {

					$substatement = 'this.hits > ' . $hits;
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

					$substatement = 'this.hits < ' . $hits;
				} else {
					//@TODO handle fast cache here... not supported yet
				}
				break;
			case 'Foomo\Cache\Persistence\Expr::expirationBefore':
				$timestamp = $parameters[0];
				$cache = 'queryable';
				if (count($parameters) > 1) {
					$cache = $parameters[1];
				}
				if ($cache == 'queryable') {
					$substatement = 'this.expirationTime < ' . $timestamp;
				} else {
					$substatement = 'this.expirationTimeFast < ' . $timestamp;
				}
				break;
			case 'Foomo\Cache\Persistence\Expr::expirationAfter':
				$timestamp = $parameters[0];
				$cache = 'queryable';
				if (count($parameters) > 1) {
					$cache = $parameters[1];
				}

				if ($cache == 'queryable') {
					$substatement = 'this.expirationTime > ' . $timestamp;
				} else {
					$substatement = 'this.expirationTime > ' . $timestamp;
				}
				break;
			default:
				break;
		}
		return $substatement;
	}

}