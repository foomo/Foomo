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
class Utils
{
	public static function collectJobs()
	{
		$jobLists = \Foomo\AutoLoader::getClassesByInterface(__NAMESPACE__ . '\\JoblistInterface');
		$jobs = array();
		foreach($jobLists as $joblistClassName) {
			$moduleName = \Foomo\Modules\Manager::getClassModule($joblistClassName);
			if(!isset($jobs[$moduleName])) {
				$jobs[$moduleName] = array();
			}
			$jobs[$moduleName] = array_merge($jobs[$moduleName], call_user_func_array(array($joblistClassName, 'getJobs'), array()));
		}
		return $jobs;
	}
	/**
	 * get the status of a job
	 * 
	 * @param \Foomo\Jobs\AbstractJob $job
	 * 
	 * @return string
	 */
	public static function getStatus(AbstractJob $job)
	{
		return 'not implemented yet';
	}
	public static function getExecutionSecret()
	{
		$executionSecretFilename = self::getExecutionSecretFilename();
		if(!file_exists($executionSecretFilename)) {
			self::makeExecutionSecret();
		}
		return file_get_contents($executionSecretFilename);
	}
	private static function makeExecutionSecret()
	{
		file_put_contents(self::getExecutionSecretFilename(), uniqid('', true) . '-' . uniqid('', true) . '-' . uniqid('', true) );
	}
	/**
	 * 
	 * @return string
	 */
	private static function getExecutionSecretFilename()
	{
		return \Foomo\Config::getVarDir(\Foomo\Module::NAME) . DIRECTORY_SEPARATOR . 'job-execution-secret.txt';
	}
	public static function getCrontab()
	{
		$crontab = new Crontab(self::collectJobs(), self::getExecutionSecret());
		return $crontab->getCrontab();
	}
	public static function installCrontab()
	{
		$crontab = new Crontab(self::collectJobs(), self::getExecutionSecret());
		$crontab->installCrontab();
	}
}