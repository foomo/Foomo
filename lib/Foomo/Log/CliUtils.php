<?php

/*
 * bestbytes-copyright-placeholder
 */

namespace Foomo\Log;

class CliUtils {

	/**
	 *
	 * @var Utils
	 */
	private $utils;

	public function __construct()
	{
		$this->utils = new Utils();
	}

	/**
	 * print all log from a file
	 *
	 * @param string $file log file to print
	 */
	public function printEntries($file)
	{
		$this->utils->setFile($file);
		return $this->utils->printEntries();
	}

	/**
	 * print session summaries
	 * 
	 * @param string $file log file
	 * 
	 * @return string
	 */
	public function printSessions($file)
	{
		$this->utils->setFile($file);
		return $this->utils->printSessions();
	}

}