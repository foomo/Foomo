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

namespace Foomo\Router;


/**
 * a router
 *
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
class Regex implements RouteMatcherInterface
{
	private $regex;
	public $parameters = array();
	public $optionalParameters = array();
	public function __construct($regex)
	{
		$parts = explode('?P<', $regex);
		if(count($parts) == 1) {
			// no named parameters
			throw new \InvalidArgumentException('you regex does not contain named parameters');
		}
		foreach(array_slice($parts,1) as $part) {
			$this->parameters[] = substr($part, 0, strpos($part, '>'));
		}
		$this->regex = '/' . $regex . '/';
	}
	/**
	 * do we match a a path
	 *
	 * @param string $path
	 *
	 * @return bool
	 */
	public function matches($path)
	{
		return (bool) preg_match($this->regex, $path);
	}
	/**
	 * extract parameters
	 *
	 * @param string $path
	 *
	 * @return array
	 */
	public function extractParameters($path)
	{
		$parameters = array();
		if(preg_match($this->regex, $path, $matches)) {
			foreach($this->parameters as $parameterName) {
				$parameters[] = $matches[$parameterName];
			}
		}
		return $parameters;
	}
	public function resolvePath($path)
	{
		return $path;
	}
}