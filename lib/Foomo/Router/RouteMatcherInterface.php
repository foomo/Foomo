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
interface RouteMatcherInterface
{
	/**
	 * do we match a a path
	 *
	 * @param string $path
	 *
	 * @return bool
	 */
	function matches($path);
	/**
	 * extract parameters
	 *
	 * @param string $path
	 *
	 * @return array
	 */
	function extractParameters($path);
	function resolvePath($path);
	/**
	 *
	 * @param array $namedParameters
	 * @param array $optionalParameters
	 *
	 * @return string
	 */
	public function url(array $namedParameters = array(), array $optionalParameters = array());

}