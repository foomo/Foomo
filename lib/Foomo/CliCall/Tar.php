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
class Tar extends \Foomo\CliCall
{
	//---------------------------------------------------------------------------------------------
	// ~ Variables
	//---------------------------------------------------------------------------------------------

	/**
	 * @var string
	 */
	private $directory;

	//---------------------------------------------------------------------------------------------
	// ~ Constructor
	//---------------------------------------------------------------------------------------------

	/**
	 *
	 */
	public function __construct()
	{
		parent::__construct('tar');
	}

	//---------------------------------------------------------------------------------------------
	// ~ Public methods
	//---------------------------------------------------------------------------------------------

	/**
	 * @return \Foomo\CliCall\Tar
	 */
	public function createArchive()
	{
		return $this->addArguments(array('-c'));
	}

	/**
	 * @return \Foomo\CliCall\Tar
	 */
	public function extractArchive()
	{
		return $this->addArguments(array('-x'));
	}

	/**
	 * @return \Foomo\CliCall\Tar
	 */
	public function compress()
	{
		return $this->addArguments(array('-z'));
	}

	/**
	 * Use archive file or device ARCHIVE
	 *
	 * @return \Foomo\CliCall\Tar
	 */
	public function file()
	{
		return $this->addArguments(array('-f'));
	}

	/**
	 * @return \Foomo\CliCall\Tar
	 */
	public function verbose()
	{
		return $this->addArguments(array('-v'));
	}

	/**
	 * Remove each file prior to extracting over it
	 *
	 * @return \Foomo\CliCall\Tar
	 */
	public function unlinkFirst()
	{
		return $this->addArguments(array('--unlink-first'));
	}

	/**
	 * Empty hierarchies prior to extracting directory
	 *
	 * @return \Foomo\CliCall\Tar
	 */
	public function recursiveUnlink()
	{
		return $this->addArguments(array('--recursive-unlink'));
	}

	/**
	 * Remove files after adding them to the archive
	 *
	 * @return \Foomo\CliCall\Tar
	 */
	public function removeFiles()
	{
		return $this->addArguments(array('--remove-files'));
	}

	/**
	 * @param string $directory
	 * @return \Foomo\CliCall\Tar
	 */
	public function directory($directory)
	{
		$this->directory = $directory;
		return $this->addArguments(array('-C', $directory));
	}

	/**
	 * @param string[] $exclude
	 * @return \Foomo\CliCall\Tar
	 */
	public function addDirectoryFiles($exclude=array('.', '..'))
	{
		if (is_null($this->directory)) trigger_error('You need to call directory($directory) first', \E_USER_ERROR);
		return $this->addArguments(\array_values(\array_diff(\scandir($this->directory), $exclude)));
	}

	//---------------------------------------------------------------------------------------------
	// ~ Overriden methods
	//---------------------------------------------------------------------------------------------

	/**
	 * @param array $arguments
	 * @return \Foomo\CliCall\Tar
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
	 * @return \Foomo\CliCall\Tar
	 */
	public static function create()
	{
		return new self();
	}
}