<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Foomo\CliCall;

/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author franklin <franklin@weareinteractive.com>
 */
class Zip extends \Foomo\CliCall
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
		parent::__construct('zip');
	}

	//---------------------------------------------------------------------------------------------
	// ~ Public methods
	//---------------------------------------------------------------------------------------------

	/**
	 * @param string[] $sources
	 * @return Foomo\CliCall\Zip
	 */
	public function addSources(array $sources)
	{
		$this->sources = array_unique(array_merge($this->sources, $sources));
		return $this;
	}

	/**
	 * @return Foomo\CliCall\Zip
	 */
	public function createZip()
	{
		$this->addArguments(array($this->filename));
		$this->addArguments($this->sources);
		$this->execute();
		return $this;
	}
}