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

namespace Foomo\Jobs\Common;

use Foomo\Jobs\AbstractJob;
/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author Jan Halfar jan@bestbytes.com
 * @author franklin <franklin@weareinteractive.com>
 */
class FileGC extends AbstractJob
{
	//---------------------------------------------------------------------------------------------
	// ~ Variables
	//---------------------------------------------------------------------------------------------

	/**
	 * @var string
	 */
	protected $executionRule = '0	*	*	*	*';
	/**
	 * scan subdirectories recursively for garbage or not
	 *
	 * @var boolean
	 */
	protected $recursive = false;
	/**
	 * how many seconds after its last change will a file be removed
	 *
	 * @var integer
	 */
	protected $maxAge = 3600;
	/**
	 * which directories to scan for garbage
	 *
	 * @var string[]
	 */
	protected $directories = array();
	/**
	 * protected directories will not be removed
	 *
	 * @var string[]
	 */
	protected $protectedDirectories = array();

	//---------------------------------------------------------------------------------------------
	// ~ Public methods
	//---------------------------------------------------------------------------------------------

	/**
	 * @return string
	 */
	public function getDescription()
	{
		return
			'will remove files older than ' . $this->maxAge . ' s ' .
			' from the directories ' . implode(', ', $this->directories) .
			' while keeping the directories ' . implode(', ', $this->protectedDirectories)
		;
	}

	/**
	 * @return string
	 */
	public function getId()
	{
		return sha1(
			__CLASS__ .
			implode(',', array_merge($this->directories, $this->protectedDirectories)) .
			$this->recursive?'rec':'nonrec' .
			$this->maxAge
		);
	}

	/**
	 *
	 */
	public function run()
	{
		# find files with modification date is older than X seconds
		$cmd = \Foomo\CliCall\Find::create($this->directories);
		if (!$this->recursive) $cmd->maxDepth(1);
		$cmd->type('f')->mmin('+' . ($this->maxAge / 60))->execute();

		# delete empty folders
		if(!empty($cmd->stdOut)) foreach(\explode(\PHP_EOL, $cmd->stdOut) as $file) \unlink($file);

		# find empty folders
		$cmd = \Foomo\CliCall\Find::create($this->directories);
		if (!$this->recursive) $cmd->maxDepth(1);
		$cmd->type('d')->addEmpty()->execute();

		# delete empty folders
		if(!empty($cmd->stdOut)) foreach(\array_diff(\explode(\PHP_EOL, $cmd->stdOut), $this->protectedDirectories) as $dir) \rmdir($dir);
	}

	/**
	 * directories to be cleaned from garbage
	 *
	 * @param string[] $directories
	 *
	 * @return \Foomo\Jobs\Common\FileGC
	 */
	public function addDirectories(array $directories)
	{
		$this->addRealPathsToProp($directories, 'directories');
		return $this;
	}

	/**
	 * directories that can be emptied, but must not be deleted
	 *
	 * @param string[] $protectedDirectories
	 *
	 * @return \Foomo\Jobs\Common\FileGC
	 */
	public function addProtectedDirectories(array $protectedDirectories)
	{
		$this->addRealPathsToProp($protectedDirectories, 'protectedDirectories');
		return $this;
	}

	/**
	 * be recursive
	 *
	 * @param boolean $recursive
	 *
	 * @return \Foomo\Jobs\Common\FileGC
	 */
	public function recursive($recursive = true)
	{
		$this->recursive = $recursive;
		return $this;
	}

	/**
	 * set the max age for files
	 *
	 * @param integer $age
	 *
	 * @return \Foomo\Jobs\Common\FileGC
	 */
	public function maxAge($age = 3600)
	{
		$this->maxAge = $age;
		return $this;
	}

	/**
	 * create one
	 *
	 * @return Foomo\Jobs\Common\FileGC
	 */
	public static function create()
	{
		return new self;
	}

	//---------------------------------------------------------------------------------------------
	// Private methods
	//---------------------------------------------------------------------------------------------

	/**
	 * @param string[] $paths
	 * @param string $prop
	 */
	private function addRealPathsToProp(array $paths, $prop)
	{
		foreach($paths as $path) {
			$real = realpath($path);
			if(!in_array($real, $this->{$prop})) {
				$this->{$prop}[] = $real;
			}
		}
	}
}