<?php

namespace Foomo\CliCall;

class Mkdir extends \Foomo\CliCall
{
	//---------------------------------------------------------------------------------------------
	// ~ Constructor
	//---------------------------------------------------------------------------------------------

	/**
	 * @param string $path
	 */
	public function __construct($path, $recursive=true)
	{
		parent::__construct('mkdir', ($recursive) ? array('-p', $path) : array($path));
	}

	//---------------------------------------------------------------------------------------------
	// ~ Public static methods
	//---------------------------------------------------------------------------------------------

	/**
	 *
	 * @param string $path
	 * @param boolean $recursive
	 * @return Foomo\CliCall\Mkdir
	 */
	public static function create($path, $recursive=true)
	{
		return new self($path, $recursive=true);
	}
}