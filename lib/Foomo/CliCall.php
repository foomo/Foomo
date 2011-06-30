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

namespace Foomo;

/**
 * cli calls made less painful
 * 
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
class CliCall
{
	//---------------------------------------------------------------------------------------------
	// ~ Variables
	//---------------------------------------------------------------------------------------------

	/**
	 * catches the std output from the command
	 *
	 * @var string
	 */
	public $stdOut;
	/**
	 * catches std err from the command call
	 *
	 * @var string
	 */
	public $stdErr;
	/**
	 * a report of the last command run
	 *
	 * @var string
	 */
	public $report;
	/**
	 * exit status
	 *
	 * @var integer
	 */
	public $exitStatus;
	/**
	 * if you want to take a look, what the last call looked like - it is here after the execute call
	 *
	 * @var string
	 */
	public $lastCommandExecuted;
	/**
	 * execution time real
	 *
	 * @var float
	 */
	public $timeReal;
	/**
	 * execution time sys
	 *
	 * @var float
	 */
	public $timeSys;
	/**
	 * execution time user
	 *
	 * @var float
	 */
	public $timeUser;
	/**
	 * resolved command to call
	 *
	 * @var string
	 */
	public $cmd;
	/**
	 * all the arguments to pass to the call
	 *
	 * @var array
	 */
	public $arguments;
	/**
	 * env to inflate, before call
	 *
	 * @var array
	 */
	public $envVars = array();

	//---------------------------------------------------------------------------------------------
	// ~ Constructor
	//---------------------------------------------------------------------------------------------

	/**
	 * construct your command
	 *
	 * @param string $cmd name or abolute path of the command, if the program file does not exist, we will call which to find it
	 * @param array $arguments array of arguments
	 * @param array $envVars array('varName' => value, ...)
	 */
	public function __construct($cmd, $arguments=array(), $envVars=array())
	{
		$this->cmd = $this->resolveCommand($cmd);
		$this->arguments = $arguments;
		$this->envVars = $envVars;
	}

	//---------------------------------------------------------------------------------------------
	// ~ Public methods
	//---------------------------------------------------------------------------------------------

	/**
	 * @param array $arguments
	 * @return Foomo\CliCall
	 */
	public function addEnvVars(array $envVars)
	{
		$this->envVars = array_merge($this->envVars, $envVars);
		return $this;
	}

	/**
	 * @param array $arguments
	 * @return Foomo\CliCall
	 */
	public function addArguments(array $arguments)
	{
		$this->arguments = array_merge($this->arguments, $arguments);
		return $this;
	}

	/**
	 * execute the command line call
	 * @return Foomo\CliCall
	 */
	public function execute()
	{
		$cmd = '';
		foreach ($this->envVars as $name => $value) {
			$cmd .= 'export ' . $name . '=' . escapeshellarg($value) . ' ; ';
		}
		$tempDir = Config::getTempDir();
		$cleanClass = \str_replace('\\', '_', __CLASS__);
		$errorTempFileName = tempnam($tempDir, $cleanClass . '-StdErr-');
		$errorTimeTempFileName = tempnam($tempDir, $cleanClass . '-StdErrTime-');
		$outTempFileName = tempnam($tempDir, $cleanClass . '-StdOut-');

		$cmd .= escapeshellarg($this->cmd);

		foreach ($this->arguments as $arg) {
			$cmd .= ' ' . escapeshellarg($arg);
		}

		$cmd .= ' 2>' . $errorTempFileName . ' 1>' . $outTempFileName;

//		$cmd = '2>' . $errorTimeTempFileName . '  time -p /bin/bash -c ' . escapeshellarg($cmd);
		$cmd = '{ time -p ' . $cmd . ' ; } 2>' . $errorTimeTempFileName;
		$this->lastCommandExecuted = $cmd;
		$outLines = array();
		$ret = exec($cmd, $outLines, $this->exitStatus);
		$this->stdErr = trim(file_get_contents($errorTempFileName));
		$this->stdOut = trim(file_get_contents($outTempFileName));
		$this->parseTime(file_get_contents($errorTimeTempFileName));
		@unlink($errorTempFileName);
		@unlink($outTempFileName);
		@unlink($errorTimeTempFileName);
		$this->updateReport();
		return $this;
	}

	//---------------------------------------------------------------------------------------------
	// ~ Private methods
	//---------------------------------------------------------------------------------------------

	/**
	 * try to resolve the given command
	 *
	 * @param string $cmd
	 */
	private function resolveCommand($cmd)
	{
		$ret = '';
		if (file_exists($cmd) || strpos($cmd, '$') === 0) {
			$ret = $cmd;
		} else {
			$resolveCmd = 'which ' . escapeshellarg($cmd);
			$ret = trim(`$resolveCmd`);
		}
		if ($ret === '') {
			throw new \Exception('command ' . $cmd . ' is invalid', 1);
		}
		return $ret;
	}

	/**
	 * update the report property, after a command was executed
	 */
	private function updateReport()
	{
		$this->report = \Foomo\Module::getView('Foomo\\CliCall', 'cliCallReport', $this)->render();
	}

	/**
	 * @param string $report
	 */
	private function parseTime($report)
	{
		$lines = explode(PHP_EOL, $report);
		foreach ($lines as $line) {
			$line = trim($line);
			$parts = explode(' ', $line);
			$type = 'time' . ucfirst($parts[0]);
			$this->$type = (float) trim($parts[count($parts) - 1]);
		}
	}

	//---------------------------------------------------------------------------------------------
	// ~ Public static methods
	//---------------------------------------------------------------------------------------------

	/**
	 * @param string $cmd name or abolute path of the command, if the program file does not exist, we will call which to find it
	 * @param array $arguments array of arguments
	 * @param array $envVars array('varName' => value, ...)
	 * @return Foomo\CliCall
	 */
	public static function create($cmd, $arguments=array(), $envVars=array())
	{
		return new self($cmd, $arguments, $envVars);
	}
}