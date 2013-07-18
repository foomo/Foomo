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
	/**
	 * callback functions when streaming std err
	 *
	 * @var function
	 */
	private $stdErrStreamCallback = array();
	/**
	 * callback functions when streaming std out
	 *
	 * @var function
	 */
	private $stdOutStreamCallback = array();

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
	public function __construct($cmd, $arguments=array(), $envVars = null)
	{
		$this->cmd = $this->resolveCommand($cmd);
		$this->arguments = $arguments;
		$this->envVars = $envVars;
	}

	//---------------------------------------------------------------------------------------------
	// ~ Public methods
	//---------------------------------------------------------------------------------------------
	/**
	 * magic method to handle timeReal, timeSys, timeUser deprecation
	 *
	 * @param string $name
	 * @return mixed
	 * @internal
	 */
	public function __get($name)
	{
		switch($name) {
			case 'timeReal':
			case 'timeSys':
			case 'timeUser':
				trigger_error('timing stats are not supported anymore', E_USER_DEPRECATED);
				break;
			default:
				trigger_error('accessing undefined property ' . $name, E_USER_NOTICE);
		}
		return null;
	}
	/**
	 * @param array $arguments
	 * @return \Foomo\CliCall
	 */
	public function addEnvVars(array $envVars)
	{
		$this->envVars = array_merge($this->envVars, $envVars);
		return $this;
	}

	/**
	 * @param array $arguments
	 * @return \Foomo\CliCall
	 */
	public function addArguments(array $arguments)
	{
		$this->arguments = array_merge($this->arguments, $arguments);
		return $this;
	}
	/**
	 * render the command string
	 *
	 * @return string
	 */
	public function renderCommand()
	{
		$cmd = $this->cmd;
		$noescape = array('<', '>', '|');
		foreach ($this->arguments as $arg) {
			$cmd .= ' ' . ((\in_array($arg, $noescape)) ? $arg : escapeshellarg($arg));
		}
		return $cmd;
	}
	/**
	 * if you expect a lot of output define callback handlers $this->stdOut will
	 * not be used
	 *
	 * @param array $callbackFunctions
	 *
	 * @return \Foomo\CliCall
	 */
	public function setStdOutStreamCallback($callbackFunction)
	{
		$this->stdOutStreamCallback = $callbackFunction;
		return $this;
	}
	/**
	 * if you expect a lot of output define callback handlers $this->stdErr will
	 * not be used
	 *
	 * @param function $callbackFunction
	 *
	 * @return \Foomo\CliCall
	 */
	public function setStdErrStreamCallback($callbackFunction)
	{
		$this->stdErrStreamCallback = $callbackFunction;
		return $this;
	}
	/**
	 * execute the command line call
	 *
	 * @return \Foomo\CliCall
	 */
	public function execute()
	{
		// setup
		$pipes = array();
		$descriptorSpec = array(
			1 => array('pipe', 'w'),
			2 => array('pipe', 'w')
		);
		$process = proc_open($this->lastCommandExecuted = $this->renderCommand(), $descriptorSpec, $pipes, $cwd = null, $this->envVars);
		if(is_resource($process)) {
			// run
			$running = true;
			while($running) {
				$status = proc_get_status($process);
				$running = $status['running'] == true;
				$this->handleStream($pipes[1], $this->stdOut, $this->stdOutStreamCallback);
				$this->handleStream($pipes[2], $this->stdErr, $this->stdErrStreamCallback);
			}
			// clean up
			proc_close($process);
			foreach($pipes as $pipe) {
				if(is_resource($pipe)) {
					fclose($pipe);
				}
			}
			// report
			$this->exitStatus = $status['exitcode'];
			$this->stdErr = trim($this->stdErr);
			$this->stdOut = trim($this->stdOut);
			$this->updateReport();
		} else {
			trigger_error('could not spawn process', E_USER_ERROR);
		}
		return $this;
	}

	//---------------------------------------------------------------------------------------------
	// ~ Private methods
	//---------------------------------------------------------------------------------------------

	private function handleStream($stream, &$target, $callback)
	{
		if($callback) {
			call_user_func_array($callback, array($stream));
		} else {
			$bytes = stream_get_contents($stream);
			if($bytes !== false && !empty($bytes)) {
				$target .= $bytes;
			}
		}
	}

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


	//---------------------------------------------------------------------------------------------
	// ~ Public static methods
	//---------------------------------------------------------------------------------------------

	/**
	 * create a cli call
	 *
	 * @param string $cmd name or abolute path of the command, if the program file does not exist, we will call which to find it
	 * @param array $arguments array of arguments
	 * @param array $envVars array('varName' => value, ...), if you set this parameter, then this will be used as
	 *       environment for the spawned process and no value will be taken from the php process environment (e.g. PATH)
	 *
	 * @return \Foomo\CliCall
	 */
	public static function create()
	{
		$args = func_get_args();
		$cmd = $args[0];
		$arguments = self::extractOptionalArg($args, 1, array());
		$envVars = self::extractOptionalArg($args, 2, null);
		return new self($cmd, $arguments, $envVars);
	}
	protected static function extractOptionalArg($argArray, $index, $default)
	{
		return (isset($argArray[$index]))?$argArray[$index]:$default;
	}

}