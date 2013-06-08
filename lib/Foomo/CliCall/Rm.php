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

namespace Foomo\CliCall;

/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author franklin <franklin@weareinteractive.com>
 */
class Rm extends \Foomo\CliCall
{
	//---------------------------------------------------------------------------------------------
	// ~ Constructor
	//---------------------------------------------------------------------------------------------

	/**
	 * @param string|string[] $file
	 */
	public function __construct($file)
	{
		parent::__construct('rm', (\is_array($file) ? $file : array($file)));
	}

	//---------------------------------------------------------------------------------------------
	// ~ Public static methods
	//---------------------------------------------------------------------------------------------

	/**
	 * @return \Foomo\CliCall\Rm
	 */
	public function force()
	{
		return $this->addArguments(array('-f'));
	}

	/**
	 * @return \Foomo\CliCall\Rm
	 */
	public function recursive()
	{
		return $this->addArguments(array('-r'));
	}

	//---------------------------------------------------------------------------------------------
	// ~ Overriden methods
	//---------------------------------------------------------------------------------------------

	/**
	 * @param array $arguments
	 * @return \Foomo\CliCall\Rm
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
	 * @param string|string[] $file
	 *
	 * @return \Foomo\CliCall\Rm
	 */
	public static function create()
	{
		$file = func_get_arg(0);
		return new self($file);
	}
}