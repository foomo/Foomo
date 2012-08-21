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
class Runner
{
	/**
	 * run a job
	 * 
	 * @param string $jobId
	 * 
	 * @throws \InvalidArgumentException
	 */
	public static function runJob($jobId)
	{
		// @todo have a sutdown hook for fatalities ...
		// http://de.php.net/register_shutdown_function
		// @example foomo logger shutdownListener
		$executionSecret = Utils::getExecutionSecret();
		foreach(Utils::collectJobs() as $module => $jobs) {
			foreach($jobs as $job) {
				/* @var $job AbstractJob */
				if($job->getSecretId($executionSecret) == $jobId) {
					ini_set('max_execution_time', $job->getMaxExecutionTime());
					ini_set('memory_limit', $job->getMemoryLimit());
					if($job->getLock()) {
						// use for the lock name in var lock-
						$job->getId();
						// try to obtain the lock
						// you can not get it
						// exit gracefully if lock not older than max execution time
						// die fatally throw runtimeexception saying why
					}
					trigger_error('running job ' . get_class($job) . ' ' . $job->getDescription() . $jobId);
					call_user_func_array(array($job, 'run'), array());
					trigger_error('done running job ' . get_class($job) . ' ' . $jobId);
					if($job->getLock()) {
						// clean up
					}
					return;
				}
			}
		}
		throw new \InvalidArgumentException('given job was not found ' . $jobId);
	}
}