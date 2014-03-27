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
class Path implements RouteMatcherInterface
{
	/**
	 * @var string
	 */
	public $command;
	/**
	 * hash name => array('prefix' => '...', 'postfix' => '...')
	 * @var array
	 */
	public $parameters = array();
	public $optionalParameters = array();
	private $generalParameterMatch = false;
	private $ignoreAfterNumberOfParameters = null;
	public function __construct($path)
	{
		$parts = explode('/', $path);
		if(count($parts) > 1 && !empty($parts[1])) {
			$this->command = $parts[1];
			for($i = 2; $i < count($parts); $i++) {
				$parameterParts = explode(':', $parts[$i]);
				if(count($parameterParts) == 2) {
					$this->parameters[$parameterParts[1]] = array('prefix' => $parameterParts[0], 'postfix' => '');
				} else if(count($parameterParts) == 1 && $parameterParts[0] == '*') {
					$this->generalParameterMatch = true;
					$this->ignoreAfterNumberOfParameters = $i - 2;
					// should we abort looping the parameters in this case ?!
				} else if(count($parameterParts) == 3) {
					// prefix and postfix
					$this->parameters[$parameterParts[1]] = array('prefix' => $parameterParts[0], 'postfix' => $parameterParts[2]);
				} else {
					throw new \InvalidArgumentException('invalid path');
				}
			}
		} else if(count($parts) == 2 && $parts[1] == '') {
			// slash special case
			$this->command = '/';
		} else if(count($parts) == 1 && $parts[0] == '') {
			$this->command = '';
		} else {
			// * case
			// will always match
			$this->command = '*';
		}
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
		if($this->command == '*') {
			return true;
		} else if(in_array($this->command, array('', '/')) && $path == $this->command) {
			return true;
		} else if(!in_array($this->command, array('*', '/'))) {
			$parts = explode('/', $path);
			if(
				(((count($this->parameters) + 1 == count($parts) - 1) ) && ($parts[1] == $this->command))
				||
				(count($parts )> 1 && $parts[1] == $this->command && $this->generalParameterMatch)
			) {
				// check params
				if($this->generalParameterMatch) {
					return true;
				} else {
					$i = 2;
					foreach(array_values($this->parameters) as $preAndPostfix) {
						$value = $parts[$i ++];
						$prefix = $preAndPostfix['prefix'];
						$postfix = $preAndPostfix['postfix'];
						if(!empty($prefix) || !empty($postfix)) {
							$lengthPrefix = strlen($prefix);
							$lengthPostfix = strlen($postfix);
							if(strlen($value) < $lengthPrefix + $lengthPostfix) {
								return false;
							} else {
								if(!empty($prefix)) {
									if(substr($value, 0, $lengthPrefix) != $prefix) {
										return false;
									}
								}
								if(!empty($postfix)) {
									if(substr($value, - $lengthPostfix) != $postfix) {
										return false;
									}
								}
							}
						}
					}
					return true;
				}
			} else {
				return false;
			}
		} else {
			return false;
		}
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
		$url = '/' . $this->command;
		foreach($this->parameters as $name => $preAndPostfix) {

			if(in_array($name, $optionalParameters) && !empty($namedParameters[$name]) || !in_array($name, $optionalParameters)) {
				$url .= '/' . $preAndPostfix['prefix'] . urlencode($namedParameters[$name]) . $preAndPostfix['postfix'];
			}
		}
		return $url;
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
		$ret = array();
		$parts = explode('/', $path);
		if(count($this->parameters) + 1 == count($parts) - 1 && $parts[1] == $this->command) {
			// check params
			$i = 2;
			foreach($this->parameters  as $name => $preAndPostfix) {
				$value = substr($parts[$i ++], strlen($preAndPostfix['prefix']));
				$lengthPostfix = strlen($preAndPostfix['postfix']);
				if($lengthPostfix > 0) {
					$value = substr($value, 0, - $lengthPostfix);
				}
				$ret[$name] = urldecode($value);
			}
		}
		return array_merge($ret, $_REQUEST);
	}
	public function resolvePath($path)
	{
		if($this->command == '*') {
			return '';
		} else if($this->command == '/') {
			return '/';
		} else {
			if(isset($this->ignoreAfterNumberOfParameters)) {
				$parts = explode('/', $path);
				return '/' . implode('/', array_slice($parts, 1, $this->ignoreAfterNumberOfParameters + 1));
			} else {
				return $path;
			}
		}
	}
}