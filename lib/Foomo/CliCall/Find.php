<?php
/*
 * This file is part of the foomo Opensource Framework.
 *
 * The foomo Opensource Framework is free software: you can redistribute it
 * and/or modify it under the terms of the GNU Lesser General Public License as
 * published  by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * The foomo Opensource Framework is distributed in the hope that it will
 * be useful, but WITHOUT ANY WARRANTY; without even the implied warranty
 * of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License along with
 * the foomo Opensource Framework. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Foomo\CliCall;

/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author franklin <franklin@weareinteractive.com>
 */
class Find extends \Foomo\CliCall
{
	//---------------------------------------------------------------------------------------------
	// ~ Constructor
	//---------------------------------------------------------------------------------------------

	/**
	 * @param string|string[] $sources
	 */
	public function __construct($sources)
	{
		if (!\is_array($sources)) $sources = array($sources);
		parent::__construct('find', $sources);
	}

	//---------------------------------------------------------------------------------------------
	// ~ Public methods
	//---------------------------------------------------------------------------------------------

	/**
	 * @param string $type
	 * @return \Foomo\CliCall\Find
	 */
	public function type($type)
	{
		return $this->addArguments(array('-type', $type));
	}

	/**
	 * @param string $name
	 * @return \Foomo\CliCall\Find
	 */
	public function name($name)
	{
		return $this->addArguments(array('-name', $name));
	}

	/**
	 * @param string $mtime
	 * @return \Foomo\CliCall\Find
	 */
	public function mtime($mtime)
	{
		return $this->addArguments(array('-mtime', $mtime));
	}

	/**
	 * @param string $ctime
	 * @return \Foomo\CliCall\Find
	 */
	public function ctime($ctime)
	{
		return $this->addArguments(array('-ctime', $ctime));
	}

	/**
	 * @param string $filename
	 * @return \Foomo\CliCall\Find
	 */
	public function newer($filename)
	{
		return $this->addArguments(array('-newer', $filename));
	}

	/**
	 * @param string $filename
	 * @return \Foomo\CliCall\Find
	 */
	public function anewer($filename)
	{
		return $this->addArguments(array('-anewer', $filename));
	}

	/**
	 * @param string $filename
	 * @return \Foomo\CliCall\Find
	 */
	public function cnewer($filename)
	{
		return $this->addArguments(array('-cnewer', $filename));
	}

	/**
	 * @return \Foomo\CliCall\Find
	 */
	public function delete()
	{
		return $this->addArguments(array('-delete'));
	}

	/**
	 * @return \Foomo\CliCall\Find
	 */
	public function exec()
	{
		return $this->addArguments(array('-exec', $value));
	}

	//---------------------------------------------------------------------------------------------
	// ~ Overriden methods
	//---------------------------------------------------------------------------------------------

	/**
	 * @param array $arguments
	 * @return \Foomo\CliCall\Find
	 */
	public function addArguments(array $arguments)
	{
		return parent::addArguments($arguments);
	}

	//---------------------------------------------------------------------------------------------
	// ~ Overriden static methods
	//---------------------------------------------------------------------------------------------

	/**
	 * create a call
	 *
	 * @param string $sources
	 * @return \Foomo\CliCall\Find
	 */
	public static function create($sources)
	{
		return new self($sources);
	}
}