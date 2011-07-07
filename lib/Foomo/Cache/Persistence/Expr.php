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

/**
 * A generic domain specific expression for querying cache resources
 * 
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
class Expr {

	/**
	 * the expression model. a nested associative array of Exprmethod names => call parameters
	 *
	 * @var array
	 */
	public $value;

	/**
	 * groups supplied expressions with logical AND operator
	 *
	 * @param Foomo\Cache\Persistence\Expr $expr1, $expr2, .. arbitrary length sequence of expressions to logically and
	 *
	 * @return Foomo\Cache\Persistence\Expr the expression containing logically ANDed grouped expression
	 */
	public static function groupAnd()
	{
		$arguments = \func_get_args();
		return self::addCondition(__METHOD__, $arguments);
	}

	/**
	 * groups supplied expressions with logical OR operator
	 *
	 * @param Foomo\Cache\Persistence\Expr $expr1, $expr2, .. arbitrary length sequence of expressions to logically or
	 *
	 * @return Foomo\Cache\Persistence\Expr the expression containing logically ORed grouped expression
	 */
	public static function groupOr()
	{
		$arguments = \func_get_args();
		return self::addCondition(__METHOD__, $arguments);
	}

	/**
	 * id equals expression
	 *
	 * @param string $id
	 *
	 * @return Foomo\Cache\Persistence\Expr
	 *
	 * @throws Exception
	 */
	public static function idEq($id)
	{
		if (!\is_string($id))
			throw new \Exception('Argument id must be string');
		$arguments = \func_get_args();
		return self::addCondition(__METHOD__, $arguments);
	}

	/**
	 * id NOT equals expression
	 *
	 * @param string $id
	 *
	 * @return Foomo\Cache\Persistence\Expr
	 *
	 * @throws Exception
	 */
	public static function idNe($id)
	{
		if (!\is_string($id))
			throw new \Exception('Argument id must be string');
		$arguments = \func_get_args();
		return self::addCondition(__METHOD__, $arguments);
	}

	/**
	 * property equals expression
	 *
	 * @param string $propertyName
	 * @param mixed $paramValue
	 *
	 * @return Foomo\Cache\Persistence\Expr
	 */
	public static function propEq($paramName, $paramValue)
	{
		$arguments = \func_get_args();
		return self::addCondition(__METHOD__, $arguments);
	}

	/**
	 * property NOT equals expression
	 *
	 * @param string $propertyName
	 * @param mixed $paramValue
	 *
	 * @return Foomo\Cache\Persistence\Expr
	 */
	public static function propNe($paramName, $paramValue)
	{
		$arguments = \func_get_args();
		return self::addCondition(__METHOD__, $arguments);
	}

	/**
	 * properties equal expression
	 *
	 * @param array $properties assoc array propName => propValue
	 *
	 * @return Foomo\Cache\Persistence\Expr
	 */
	public static function propsEq($properties)
	{
		if (count($properties) == 0)
			throw new \Exception('Properties must not be empty');

		$arguments = \func_get_args();
		return self::addCondition(__METHOD__, $arguments);
	}

	/**
	 * status is valid expression
	 *
	 * @return Foomo\Cache\Persistence\Expr
	 */
	public static function statusValid()
	{
		$arguments = \func_get_args();
		return self::addCondition(__METHOD__, $arguments);
	}

	/**
	 * status is invalid expression
	 *
	 * @return Foomo\Cache\Persistence\Expr
	 */
	public static function statusInvalid()
	{
		$arguments = \func_get_args();
		return self::addCondition(__METHOD__, $arguments);
	}

	/**
	 * resource is expired expression
	 *
	 * @param string $cache 'fast' to check the expiration in fast cache,
	 * default is 'queryable' cache
	 *
	 * @return Foomo\Cache\Persistence\Expr
	 */
	public static function isExpired($cache = 'queryable')
	{
		$arguments = func_get_args();
		return self::addCondition(__METHOD__, $arguments);
	}

	/**
	 * resource is NOT expired expression
	 *
	 * @param string $cache 'fast' to check the expiration in fast cache,
	 * default is 'queryable' cache
	 *
	 * @return Foomo\Cache\Persistence\Expr
	 */
	public static function isNotExpired($cache = 'queryable')
	{
		$arguments = func_get_args();
		return self::addCondition(__METHOD__, $arguments);
	}

	/**
	 * resource created before expression
	 *
	 * @param integer $timestamp
	 *
	 * @return Foomo\Cache\Persistence\Expr
	 *
	 * @throws Exception
	 */
	public static function createdBefore($timestamp)
	{
		if (!\is_integer($timestamp))
			throw new \Exception('Timestamp must be integer.');

		$arguments = func_get_args();
		return self::addCondition(__METHOD__, $arguments);
	}

	/**
	 * resource created after expression
	 *
	 * @param integer $timestamp
	 *
	 * @return Foomo\Cache\Persistence\Expr
	 *
	 * @throws Exception
	 */
	public static function createdAfter($timestamp)
	{
		if (!\is_integer($timestamp))
			throw new \Exception('Timestamp must be integer.');
		$arguments = func_get_args();
		return self::addCondition(__METHOD__, $arguments);
	}

	/**
	 * cache hits for resource more than expression
	 *
	 * @param int $hits
	 *
	 * @param string $cache 'fast' to check fast cache, default is 'queryable' cache
	 *
	 * @return Foomo\Cache\Persistence\Expr
	 *
	 * @throws Exception
	 */
	public static function hitsMoreThan($hits, $cache = 'queryable')
	{
		if (!\is_integer($hits))
			throw new \Exception('Hits must be integer.');
		$arguments = func_get_args();
		return self::addCondition(__METHOD__, $arguments);
	}

	/**
	 * cache hits for resource more than expression
	 *
	 * @param int $hits
	 *
	 * @param string $cache 'fast' to check fast cache, default is 'queryable' cache
	 *
	 * @return Foomo\Cache\Persistence\Expr
	 *
	 * @throws Exception
	 */
	public static function hitsLessThan($hits, $cache = 'queryable')
	{
		$arguments = \func_get_args();
		if (!\is_integer($hits))
			throw new \Exception('Hits must be integer.');
		return self::addCondition(__METHOD__, $arguments);
	}

	/**
	 * resource expiration after expression
	 *
	 * @param integer $expirationTime timestamp
	 *
	 * @param string $cache 'fast' to check fast cache, default is 'queryable' cache
	 *
	 * @return Foomo\Cache\Persistence\Expr
	 *
	 * @throws Exception
	 */
	public static function expirationAfter($expirationTime, $cache = 'queryable')
	{
		if (!\is_integer($expirationTime))
			throw new \Exception('expirationTime must be integer.');
		$arguments = \func_get_args();
		return self::addCondition(__METHOD__, $arguments);
	}

	/**
	 * resource expiration before expression
	 *
	 * @param integer $expirationTime timestamp
	 * @param string $cache 'fast' to check fast cache, default is 'queryable' cache
	 *
	 * @return Foomo\Cache\Persistence\Expr
	 *
	 * @throws Exception
	 */
	public static function expirationBefore($expirationTime, $cache = 'queryable')
	{
		if (!\is_integer($expirationTime))
			throw new \Exception('expirationTime must be integer.');
		$arguments = \func_get_args();
		return self::addCondition(__METHOD__, $arguments);
	}

	private static function addCondition($methodCall, $parameters)
	{
		$expr = new self;
		$expr->value = array($methodCall, $parameters);
		return $expr;
	}
}