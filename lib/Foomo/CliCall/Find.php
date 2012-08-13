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
	 * @param string $source
	 */
	public function __construct($source)
	{
		parent::__construct('find', array($source));
	}

	//---------------------------------------------------------------------------------------------
	// ~ Public methods
	//---------------------------------------------------------------------------------------------

	/**
	 * @param string $value
	 * @return Foomo\CliCall\Find
	 */
	public function type($value)
	{
		return $this->addArguments(array('-type', $value));
	}

	/**
	 * @param string $value
	 * @return Foomo\CliCall\Find
	 */
	public function name($value)
	{
		return $this->addArguments(array('-name', $value));
	}

	/**
	 * @param string $value
	 * @return Foomo\CliCall\Find
	 */
	public function mtime($value)
	{
		return $this->addArguments(array('-mtime', $value));
	}

	/**
	 * @return Foomo\CliCall\Find
	 */
	public function delete()
	{
		return $this->addArguments(array('-delete'));
	}

	/**
	 * @return Foomo\CliCall\Find
	 */
	public function exec()
	{
		return $this->addArguments(array('-exec', $value));
	}

	//---------------------------------------------------------------------------------------------
	// ~ Public static methods
	//---------------------------------------------------------------------------------------------

	/**
	 * create a call
	 *
	 * @param string $source
	 *
	 * @return Foomo\CliCall\Find
	 */
	public static function create($source)
	{
		return new self($source = func_get_arg(0));
	}
}