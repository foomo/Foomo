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
 */
class FileGC extends AbstractJob
{
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
	public function getDescription()
	{
		return 
			'will remove files older than ' . $this->maxAge . ' s ' . 
			' from the directories ' . implode(', ', $this->directories) .
			' while keeping the directories ' . implode(', ', $this->protectedDirectories)
		;
	}
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
	private function addRealPathsToProp(array $paths, $prop)
	{
		foreach($paths as $path) {
			$real = realpath($path);
			if(!in_array($real, $this->{$prop})) {
				$this->{$prop}[] = $real;
			}
		}
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
	/**
	 * decide whether or not a file is garbage
	 * you might want to verride this ...
	 * 
	 * @param \SplFileInfo $file
	 * 
	 * @return boolean
	 */
	protected function getFileIsGarbage(\SplFileInfo $file)
	{
		return (time() - $file->getCTime()) > $this->maxAge;
	}
	public function run()
	{
		foreach($this->directories as $dir) {
			$this->crawl(new \DirectoryIterator($dir));
		}
	}
	protected function crawl(\DirectoryIterator $dir)
	{
		$dirName = realpath($dir->getPathname());
		/* @var $fileInfo SplFileInfo */
		foreach($dir as $fileInfo) {
			if(!$fileInfo->isDot()) {
				if($fileInfo->isDir()) {
					$subDir = new \DirectoryIterator(realpath($fileInfo->getPathname()));
					if($this->recursive) {
						$this->crawl($subDir);
					}
				} else if($fileInfo->isFile()) {
					if($this->getFileIsGarbage($fileInfo)) {
						unlink($fileInfo->getPathname());
					}
				}
			}
		}
		$this->tryRMDir($dirName);		
	}
	private function tryRMDir($dir)
	{
		if(
			!in_array($dir, $this->protectedDirectories) &&
			!in_array($dir, $this->directories)
		) {
			$fileCount = 0;
			foreach(new \DirectoryIterator($dir) as $fileInfo) {
				if(!$fileInfo->isDot()) {
					$fileCount ++;
				}
			}
			if($fileCount == 0) {
				rmdir($dir);
			}
		}
	}
}