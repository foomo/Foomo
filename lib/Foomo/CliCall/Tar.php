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
	 * The output tgz filename
	 *
	 * @var string
	 */
	public $filename;
	/**
	 * Current directory name
	 *
	 * @var string
	 */
	public $dirname;
	/**
	 * file
	 *
	 * @var string[]
	 */
	public $sources = array();

	//---------------------------------------------------------------------------------------------
	// ~ Constructor
	//---------------------------------------------------------------------------------------------

	/**
	 * @param string $filename
	 */
	public function __construct($filename)
	{
		if (file_exists($filename)) trigger_error('File ' . $filename . ' already exist!', \E_USER_ERROR);
		if (!\is_writable(\dirname($filename))) trigger_error('Folder ' . \dirname($filename) . ' is not writeable!', \E_USER_ERROR);
		$this->filename = $filename;
		parent::__construct('tar');
	}

	//---------------------------------------------------------------------------------------------
	// ~ Public methods
	//---------------------------------------------------------------------------------------------

	/**
	 * @param string $dirname
	 * @return Foomo\CliCall\Tar
	 */
	public function moveIntoDirectory($dirname)
	{
		if (!file_exists($dirname)) throw new \Exception('Directory ' . $dirname . ' does not exist!');
		$this->dirname = $dirname;
		$this->addArguments(array('--directory', $this->dirname));
		return $this;
	}

	/**
	 * @param string[] $exclude
	 * @return Foomo\CliCall\Tar
	 */
	public function addDirectoryFiles($exclude=array('.', '..'))
	{
		if (is_null($this->dirname)) throw new \Exception('You need to call moveIntoDirectory() first');
		return $this->addSources(array_values(array_diff(\scandir($this->dirname), $exclude)));
	}

	/**
	 * @param string[] $sources
	 * @return Foomo\CliCall\Tar
	 */
	public function addSources(array $sources)
	{
		$this->sources = array_unique(array_merge($this->sources, $sources));
		return $this;
	}

	/**
	 * @return Foomo\CliCall\Tar
	 */
	public function createTgz()
	{
		$this->addArguments(array('-czvf', $this->filename));
		$this->addArguments($this->sources);
		$this->execute();
		return $this;
	}

	/**
	 * @return Foomo\CliCall\Tar
	 */
	public function createTar()
	{
		$this->addArguments(array('-cvf', $this->filename));
		$this->addArguments($this->sources);
		$this->execute();
		return $this;
	}

	//---------------------------------------------------------------------------------------------
	// ~ Public static methods
	//---------------------------------------------------------------------------------------------

	/**
	 * create a call
	 * 
	 * @param string $filename
	 * 
	 * @return Foomo\CliCall\Tar
	 */
	public static function create()
	{
		
		return new self($filename = func_get_arg(0));
	}
}