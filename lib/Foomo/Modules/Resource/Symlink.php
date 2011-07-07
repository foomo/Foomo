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

namespace Foomo\Modules\Resource;

/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author franklin <franklin@weareinteractive.com>
 */
class Symlink extends \Foomo\Modules\Resource
{
	//---------------------------------------------------------------------------------------------
	// ~ Variables
	//---------------------------------------------------------------------------------------------

	/**
	 * @var string
	 */
	private $target;
	/**
	 * @var string
	 */
	private $link;

	//---------------------------------------------------------------------------------------------
	// ~ Constructor
	//---------------------------------------------------------------------------------------------

	/**
	 * @param string $target target file or directory
	 * @param string $link name of the symbolic link
	 */
	private function __construct($target, $link)
	{
		$this->target = $target;
		$this->link = $link;
	}

	//---------------------------------------------------------------------------------------------
	// ~ Public methods
	//---------------------------------------------------------------------------------------------

	/**
	 * @return string
	 */
	public function getTarget()
	{
		return $this->target;
	}

	/**
	 * @return string
	 */
	public function getAbsoluteTarget()
	{
		if (substr($this->target, 0, 1) == DIRECTORY_SEPARATOR) {
			return $this->target;
		} else {
			return realpath(substr($this->link, 0, strripos($this->link, DIRECTORY_SEPARATOR)) . DIRECTORY_SEPARATOR . $this->target);
		}
	}

	/**
	 * @return string
	 */
	public function getLink()
	{
		return $this->link;
	}

	/**
	 * @return string
	 */
	public function getDirectory()
	{
		return $this->directory;
	}

	/**
	 * @return boolean
	 */
	public function resourceValid()
	{
		return (file_exists($this->link) && is_link($this->link) && readlink($this->link) == $this->target && file_exists($this->getAbsoluteTarget()));
	}

	/**
	 * @return string
	 */
	public function resourceStatus()
	{
		$ret = 'Symbolic link from "' . $this->link . '" to "' . $this->target . '" (' . $this->getAbsoluteTarget() . ')';
		return $ret . ((!$this->resourceValid()) ? ' is missing' : ' is ok');
	}

	/**
	 * @return string
	 */
	public function tryCreate()
	{
		if ($this->resourceValid()) {
			return ' symbolic link "' . $this->link . '" is valid';
		} else if (!file_exists($this->getAbsoluteTarget())) {
			return ' target ' . $this->getAbsoluteTarget() . ' does not exist!';
		} else {
			$ret = ' trying to create symbolik link';
			$ret .= (@symlink($this->target, $this->link)) ? ' - success' : '- failed : ' . error_get_last();
			return $ret;
		}
	}

	//---------------------------------------------------------------------------------------------
	// ~ Public static methods
	//---------------------------------------------------------------------------------------------

	/**
	 * @param string $target target file or directory
	 * @param string $link name of the symbolic link
	 * @return Foomo\Modules\Resource\Symlink
	 */
	public static function getResource($target, $link)
	{
		return new self($target, $link);
	}
}