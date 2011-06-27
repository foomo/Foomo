<?php

namespace Foomo\CliCall;

class Rm extends \Foomo\CliCall
{
	//---------------------------------------------------------------------------------------------
	// ~ Constructor
	//---------------------------------------------------------------------------------------------

	/**
	 * @param string $filename
	 */
	public function __construct($filename, $recursive=true)
	{
		parent::__construct('rm', ($recursive) ? array('-r', $filename) : array($filename));
	}

	//---------------------------------------------------------------------------------------------
	// ~ Public static methods
	//---------------------------------------------------------------------------------------------

	/**
	 *
	 * @param string $filename
	 * @return Foomo\CliCall\Rm
	 */
	public static function create($path)
	{
		return new self($path);
	}
}