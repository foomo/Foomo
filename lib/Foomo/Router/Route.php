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
class Route
{
	/**
	 * @var callback
	 */
	public $callback;
	/**
	 * @var RouteMatcherInterface
	 */
	private $matcher;

	public function __construct(RouteMatcherInterface $matcher, $callback)
	{
		$this->matcher = $matcher;
		$this->callback = $callback;
	}
	public static function createWithPath($path, $callback)
	{
		return new self(new Path($path), $callback);
	}
	public static function createWithRegexMatcher($matchingRegex, $renderingRegex, $callback)
	{
		return new self(new Regex($matchingRegex, $renderingRegex), $callback);
	}
	/**
	 * does this route match
	 *
	 * @param string $path
	 *
	 * @return bool
	 */
	public function matches($path)
	{
		return $this->matcher->matches($path);
	}

	/**
	 * does this route handle the given method ?!
	 *
	 * @param string $className
	 * @param string $methodName
	 *
	 * @return bool
	 */
	public function handlesMethod($className, $methodName)
	{
		if(is_string($this->callback[0]) && $this->callback[0] == $className || is_object($this->callback[0]) && get_class($this->callback[0]) == $className) {
			$myMethodName = $this->getMethodReflection($this->callback)->getName();
			$candidates = array($myMethodName, $this->matcher->command);
			if(substr($myMethodName, 0, 6) == 'action') {
				$candidates[] = lcfirst(substr($myMethodName, 6));
			}
			return in_array($methodName, $candidates);
		} else {
			return false;
		}
	}

	/**
	 * @param callback $callback
	 * @return bool
	 */
	public function isResponsibleFor($callback)
	{
		return $callback == $this->callback;
	}
	/**
	 * @param array $parameters
	 *
	 * @return string
	 */
	public function url($parameters = array())
	{
		$namedParameters = array();
		$i = 0;
		$optionalParameters = array();
		foreach($this->getMethodReflection($this->callback)->getParameters() as $parameterReflection) {
			if(isset($parameters[$i])) {
				$value = $parameters[$i];
			} else {
				$value = null;
			}
			$namedParameters[$parameterReflection->getName()] = $value;
			if($parameterReflection->isOptional()) {
				$optionalParameters[] = $parameterReflection->getName();
			}
			$i ++;
		}
		return $this->matcher->url($namedParameters, $optionalParameters);
	}

	/**
	 * get parameters
	 *
	 * @param string $path
	 *
	 * @return array
	 */
	public function getParameters($path)
	{
		return $this->getParametersForCallBack(
			$this->callback,
			$this->matcher->extractParameters($path)
		);
	}
	/**
	 *
	 * @param callable $callback
	 * @param array $parameters
	 *
	 * @return array
	 */
	public function getParametersForCallBack($callback, $parameters)
	{
		$parametersForCallback = array();
		foreach($this->getMethodReflection($callback)->getParameters() as $reflectionParameter) {
			if(isset($parameters[$reflectionParameter->getName()])) {
				$parametersForCallback[] = $parameters[$reflectionParameter->getName()];
			} else {
				$parametersForCallback[] = null;
			}
		}
		return $parametersForCallback;
	}

	/**
	 * @param callback $callback
	 *
	 * @return \ReflectionMethod
	 */
	private function getMethodReflection($callback)
	{
		return new \ReflectionMethod($callback[0], $callback[1]);
	}

	/**
	 * command name
	 * @return string
	 */
	public function getCallbackName()
	{
		return $this->getMethodReflection($this->callback)->getName();
	}
	/**
	 * @param string $path
	 *
	 * @return mixed whatever comes back from the callback
	 *
	 * @throws \LogicException
	 */
	public function execute($path)
	{
		if($this->matches($path)) {
			return call_user_func_array($this->callback, $this->getParameters($path));
		} else {
			// bang
			throw new \LogicException('path does not match');
		}
	}

	/**
	 * @return bool
	 */
	public function isLive()
	{
		if(is_object($this->callback[0])) {
			return true;
		} else {
			return  $this->getMethodReflection($this->callback)->isStatic();
		}
	}
	public function resolvePath($path)
	{
		return $this->matcher->resolvePath($path);
	}
}