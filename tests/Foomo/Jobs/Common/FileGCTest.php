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
 
/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author Jan Halfar jan@bestbytes.com
 */
class FileGCTest extends \PHPUnit_Framework_TestCase
{
	const ROOT_DIR_NAME = 'jobTest';
	private function getTestStructure()
	{
		return array(
			'name' => self::ROOT_DIR_NAME,
			'files' => array('1', '2'),
			'dirs' => array(
				array(
					'name' => 'foo',
					'files' => array('a', 'b'),
					'dirs' => array(
						array(
							'dirs' => array(),
							'name' => 'foo-foo',
							'files' => array('foo-a', 'foo-b')
						)
					)
				),
				array(
					'name' => 'bar',
					'files' => array('x', 'y'),
					'dirs' => array()
				),
				
			)
		);
	}
	private function getTestRootDir()
	{
		return \Foomo\Config::getTempDir();
	}
	public function setUp()
	{
		clearstatcache();
		$this->drop($this->getTestStructure(), $this->getTestRootDir());
		$this->create($this->getTestStructure(), $this->getTestRootDir());
		clearstatcache();
	}
	public function tearDown()
	{
		$this->drop($this->getTestStructure(), $this->getTestRootDir());
	}
	/**
	 * @return FileGC
	 */
	private function getGC() 
	{
		return FileGC::create()->addDirectories(array($this->getTestRootDir() . DIRECTORY_SEPARATOR . self::ROOT_DIR_NAME));
	}
	private function create(array $structure, $path)
	{
		
		$path .= DIRECTORY_SEPARATOR . $structure['name'];
		mkdir($path);
		foreach($structure['files'] as $f) {
			file_put_contents($path . DIRECTORY_SEPARATOR . $f, $f);
		}
		foreach($structure['dirs'] as $subStructure) {
			$this->create($subStructure, $path);
		}
	}
	private function drop(array $structure, $path)
	{
		$path .= DIRECTORY_SEPARATOR . $structure['name'];
		foreach($structure['dirs'] as $subStructure) {
			$this->drop($subStructure, $path);
		}
		foreach($structure['files'] as $f) {
			$file = $path . DIRECTORY_SEPARATOR . $f;
			if(file_exists($file)) {
				unlink($file);
			}
		}
		if(file_exists($path)) {
			rmdir($path);
		}
	}
	
	public function testNonRecursive()
	{
		sleep(2);
		$gc = $this->getGC()->maxAge(1)->recursive(false)->run();
		$structure = $this->getTestStructure();
		foreach($structure['files'] as $name) {
			$this->assertFileNotExists($this->getTestRootDir() . DIRECTORY_SEPARATOR . $structure['name'] . DIRECTORY_SEPARATOR . $name);
		}
		foreach($structure['dirs'][0]['files'] as $name) {
			$this->assertFileExists($this->getTestRootDir() . DIRECTORY_SEPARATOR . $structure['name'] . DIRECTORY_SEPARATOR . $structure['dirs'][0]['name'] . DIRECTORY_SEPARATOR . $name);
		}
	}
	public function testRecursive()
	{
		$root = $this->getTestRootDir() . DIRECTORY_SEPARATOR . self::ROOT_DIR_NAME;

		$structure = $this->getTestStructure();		
		$someProtectedDirs = array(
			$root . DIRECTORY_SEPARATOR . $structure['dirs'][0]['name'],
			$root . DIRECTORY_SEPARATOR . $structure['dirs'][1]['name']
		);
		$someNotProtectedDirs = array(
			$root . DIRECTORY_SEPARATOR . $structure['dirs'][0]['name'] . DIRECTORY_SEPARATOR . $structure['dirs'][0]['dirs'][0]['name']
		);
		sleep(2);		
		$gc = $this->getGC()->maxAge(1)->recursive()->addProtectedDirectories($someProtectedDirs);
		$gc->run();

		foreach($structure['files'] as $name) {
			$this->assertFileNotExists($this->getTestRootDir() . DIRECTORY_SEPARATOR . $structure['name'] . DIRECTORY_SEPARATOR . $name);
		}
		foreach($structure['dirs'][0]['files'] as $name) {
			$this->assertFileNotExists($this->getTestRootDir() . DIRECTORY_SEPARATOR . $structure['name'] . DIRECTORY_SEPARATOR . $structure['dirs'][0]['name'] . DIRECTORY_SEPARATOR . $name);
		}
		foreach($someProtectedDirs as $protectedDir) {
			$this->assertFileExists($protectedDir);
		}
		foreach($someNotProtectedDirs as $someNotProtectedDir) {
			$this->assertFileNotExists($someNotProtectedDir);
		}
	}
}