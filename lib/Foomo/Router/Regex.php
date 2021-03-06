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
	private $urlTemplate;
	public $parameters = array();
	public function __construct($regex, $urlTemplate)
	{
		if (preg_match_all('/\(\?P<(\w+)>/', $regex, $matches)) {
			$this->parameters = $matches[1];
		}
		$this->regex = $regex;
		$this->urlTemplate = $urlTemplate;
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
				$parameters[$parameterName] = $matches[$parameterName];
			}
		}
		return $parameters;
	}
	public function resolvePath($path)
	{
		return $path;
	}
	/**
	 *
	 * @param array $namedParameters
	 * @param array $optionalParameters
	 *
	 * @return string
	 */
	public function url(array $namedParameters = array(), array $optionalParameters = array())
	{
		$path = new Path($this->urlTemplate);
		return $path->url($namedParameters, $optionalParameters);
	}

}