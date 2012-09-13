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

namespace Foomo\Jobs;
 
/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author Jan Halfar jan@bestbytes.com
 */
class Crontab
{
	/**
	 * @var JobListInterface
	 */
	private $moduleJobLists;
	/**
	 * @var string
	 */
	private $executionSecret;
    public function __construct($moduleJobLists, $executionSecret)
    {
		$this->moduleJobLists = $moduleJobLists;
		$this->executionSecret = $executionSecret;
    }
	/**
	 * get my crontab id
	 * 
	 * @return string
	 */
	private function getCrontabId()
	{
		return \Foomo\ROOT . '-' . \Foomo\Config::getMode();
	}
	private function getCrontabSeparator()
	{
		return '#### foomo crontab ' . $this->getCrontabId() . ' ####';
	}
	public function getCrontab()
	{
		$crontab = '# updated @ ' . date('Y-m-d H:i:s') . PHP_EOL;
		$config = \Foomo\Config::getConf(\Foomo\Module::NAME, DomainConfig::NAME);
		if(!$config) {
			throw new \RuntimeException('can not find my domain configuration');
		}
		if($config->useCurl) {
			$args = array();
			foreach($config->curlOptions as $option) {
				$args[] = escapeshellarg($option);
			}
			$runCmd = 'curl ' . implode(' ', $args) . ' ' . \Foomo\Utils::getServerUrl() . \Foomo\Module::getHtdocsPath('jobRunner.php') . '/';
		} else {
			$shellFile = \Foomo\Setup::getShellFilename();
			$runCmd = $shellFile . ' ' . dirname($shellFile) . DIRECTORY_SEPARATOR . 'jobRunner.php ';
		}

		foreach($this->moduleJobLists as $module => $jobs) {
			/* @var $job AbstractJob */
			$crontab .= '## module ' . $module . PHP_EOL; 
			foreach($jobs as $job) {
				// headline
				$crontab .= 
					'### ' . get_class($job) . PHP_EOL . 
					$this->makeCommentedBlock('### ', explode(' ', str_replace(array(PHP_EOL, '  '), ' ', $job->getDescription()))) . PHP_EOL
				;
				$crontab .= $job->getExecutionRule() . ' ' . $runCmd . $job->getSecretId($this->executionSecret) . PHP_EOL;
			}
		}
		return $crontab;
	}
	private function makeCommentedBlock($prefix, $words)
	{
		$lines = array();
		$line = $prefix;
		foreach($words as $word) {
			$line .= $word . ' ';
			if(strlen($line) > 80) {
				$lines[] = trim($line);
				$line = $prefix;
			}
		}
		if(!empty($line)) {
			$lines[] = trim($line);
		}
		return implode(PHP_EOL, $lines);
	}
	private function getUserCrontab()
	{
		$call = \Foomo\CliCall::create('crontab', array('-l'));
		$call->execute();
		if($call->exitStatus == 0) {
			return $call->stdOut;
		} else {
			throw new \RuntimeException('could not load existing crontab');
		}
	}
	public function installCrontab()
	{
		// get crontab
		$userCrontab = $this->getUserCrontab();
		$separator = $this->getCrontabSeparator();
		// insert new one
		$parts = explode($separator, $userCrontab);
		
		if(count($parts) == 1 || count($parts) === 3) {
			$insert = 
				((substr($parts[0], -1) != PHP_EOL)?PHP_EOL:'') .
				$separator . PHP_EOL . $this->getCrontab() . 
				$separator
			;
			if(count($parts) === 3) {
				$parts[1] = $insert . ((substr($parts[2], 0, 1) != PHP_EOL)?PHP_EOL:'');
			} else {
				$parts[] = $insert . PHP_EOL;
			}
			// install it
			file_put_contents($this->getCrontabFile(), implode('', $parts));
			$call = \Foomo\CliCall::create('crontab', array($this->getCrontabFile()));
			$call->execute();
			if($call->exitStatus != 0) {
				throw new \RuntimeException('could not install crontab ' . $call->report);
			}
		} else {
			throw new \RuntimeException('do not know where to insert my crontab block in the users crontab');
		}

	}
	private function getCrontabFile()
	{
		return \Foomo\Module::getVarDir() . DIRECTORY_SEPARATOR . 'crontab';
	}
	
}