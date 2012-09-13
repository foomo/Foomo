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
class CliCommand extends \Foomo\Modules\Resource
{
	//---------------------------------------------------------------------------------------------
	// ~ Variables
	//---------------------------------------------------------------------------------------------

	/**
	 * Class name that should exist
	 *
	 * @var string
	 */
	private $command;

	//---------------------------------------------------------------------------------------------
	// ~ Constructor
	//---------------------------------------------------------------------------------------------

	/**
	 * @param string $className Cli command that should exist
	 */
	private function __construct($command)
	{
		$this->command = $command;
	}

	//---------------------------------------------------------------------------------------------
	// ~ Public methods
	//---------------------------------------------------------------------------------------------

	/**
	 * @return string
	 */
	public function getCommand()
	{
		return $this->command;
	}

	/**
	 * @return boolean
	 */
	public function resourceValid()
	{
		$filename = $this->which();
		return (!empty($filename) && \file_exists($filename) && \is_executable($filename));
	}

	/**
	 * @return string
	 */
	public function resourceStatus()
	{
		return 'Command "' . $this->command . '"' . ((!$this->resourceValid()) ? ' is missing' : ' is ok [' . $this->which() . ']');
	}

	/**
	 * @return string
	 */
	public function tryCreate()
	{
		return ' plase install ' . $this->command;
	}

	//---------------------------------------------------------------------------------------------
	// Private methods
	//---------------------------------------------------------------------------------------------

	/**
	 * @return string
	 */
	private function which()
	{
		$cmd = 'which ' . escapeshellarg($this->command);
		return \trim(`$cmd`);
	}

	//---------------------------------------------------------------------------------------------
	// ~ Public static methods
	//---------------------------------------------------------------------------------------------

	/**
	 * @param string $command Cli command that should exist
	 * @return Foomo\Modules\Resource\CliCommand
	 */
	public static function getResource($command)
	{
		return new self($command);
	}
}