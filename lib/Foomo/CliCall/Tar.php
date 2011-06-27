<?php

namespace Foomo\CliCall;

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
		if (file_exists($filename)) throw new \Exception('File ' . $filename . ' already exist!');
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
	public function addSources($sources)
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
	 *
	 * @param string $filename
	 * @return Foomo\CliCall\Tar
	 */
	public static function create($filename)
	{
		return new self($filename);
	}
}