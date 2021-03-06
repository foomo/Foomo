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
	 * @param string $atime
	 * @return \Foomo\CliCall\Find
	 */
	public function atime($atime)
	{
		return $this->addArguments(array('-atime', $atime));
	}

	/**
	 * @param string $mmin
	 * @return \Foomo\CliCall\Find
	 */
	public function mmin($mmin)
	{
		return $this->addArguments(array('-mmin', $mmin));
	}

	/**
	 * @param string $cmin
	 * @return \Foomo\CliCall\Find
	 */
	public function cmin($cmin)
	{
		return $this->addArguments(array('-cmin', $cmin));
	}

	/**
	 * @param string $amin
	 * @return \Foomo\CliCall\Find
	 */
	public function amin($amin)
	{
		return $this->addArguments(array('-amin', $amin));
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
	 *  Descend at most levels (a non-negative integer) levels of directories below the command line arguments.  -maxdepth 0
     *  means only apply the tests and actions to the command line arguments.
	 *
	 * @param integer $maxDepth
	 * @return \Foomo\CliCall\Find
	 */
	public function maxDepth($maxDepth)
	{
		return $this->addArguments(array('-maxdepth', $maxDepth));
	}

	/**
	 * File is empty and is either a regular file or a directory.
	 *
	 * @return \Foomo\CliCall\Find
	 */
	public function addEmpty()
	{
		return $this->addArguments(array('-empty'));
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

	/**
	 * @return \Foomo\CliCall\Find
	 */
	public function execute()
	{
		return parent::execute();
	}

	//---------------------------------------------------------------------------------------------
	// ~ Overriden static methods
	//---------------------------------------------------------------------------------------------

	/**
	 * create a call
	 *
	 * @param string $sources
	 *
	 * @return \Foomo\CliCall\Find
	 */
	public static function create()
	{
		$sources = func_get_arg(0);
		return new self($sources);
	}
}