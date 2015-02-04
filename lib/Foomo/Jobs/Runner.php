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
class Runner {

	/**
	 * run a job
	 * 
	 * @param string $jobId
	 * 
	 * @throws \InvalidArgumentException, \RuntimeException
	 */
	private static $callback = false;

	public static function runJob($jobSecretId) {
		$executionSecret = Utils::getExecutionSecret();
		foreach (Utils::collectJobs() as $module => $jobs) {
			foreach ($jobs as $job) {
				/* @var $job AbstractJob */
				if ($job->getSecretId($executionSecret) == $jobSecretId) {

					self::runAJob($job);

					return;
				}
			}
		}
		throw new \InvalidArgumentException('given job was not found ' . $jobSecretId);
	}

	public static function runAJob(AbstractJob $job) {
		self::$callback = true;
		$jobId = $job->getId();
		\register_shutdown_function('Foomo\Jobs\Runner::shutdownListener', array($jobId));
		set_time_limit($job->getMaxExecutionTime());
		ini_set('memory_limit', $job->getMemoryLimit());
		$locked = false;
		$pid = getmypid();
		if ($job->getLock()) {
			$locked = \Foomo\Lock::lock($jobId, $blocking = false);
			if (!$locked) {
				$lockData = \Foomo\Lock::getLockInfo($job->getId());
				if ($lockData['lock_age'] < $job->getMaxExecutionTime()) {
					trigger_error('previous run of job ' . $job->getId() . ' still running while trying to run again', E_USER_WARNING);
					Utils::updateStatusJobError($jobId, $pid, JobStatus::ERROR_ATTEMPTED_CONCURRENT_RUN, 'attempted concurent run', $isRunning = true, $isLocked = true);
					self::$callback = false;
					return;
				} else {
					Utils::updateStatusJobError($jobId, $pid, $errorCode = JobStatus::ERROR_NO_LOCK, $errorMessage = 'could not obtain lock to run job');
					Utils::updateStatusJobError($jobId, $pid, JobStatus::ERROR_NO_LOCK, 'could not obtain lock to run job', $isRunning = false, $isLocked = false);
					throw new \RuntimeException('Could not obtain lock to run job ' . $jobId);
				}
			}
		}
		Utils::updateStatusJobStart($jobId, $pid, $locked);
		try {
			call_user_func_array(array($job, 'run'), array());
			Utils::updateStatusJobDone($jobId, $pid);
		} catch(\Exception $e) {
			Utils::updateStatusJobError($jobId, $pid, $errorCode = JobStatus::ERROR_DIED, $errorMessage = $e->getMessage(), $isRunning = false, $isLocked = false);
		}

		if ($job->getLock()) {
			// clean up
			\Foomo\Lock::release($job->getId());
		}
		self::$callback = false;
	}

	public static function shutDownListener($params) {
		if (self::$callback) {
			trigger_error(__METHOD__);
			$error = error_get_last();
			if (isset($error['type']) && ($error['type'] === E_ERROR || $error['type'] === E_USER_ERROR)) {
				echo "Can do custom output and/or logging for fatal error here...";
				Utils::updateStatusJobError($params[0], getmypid(), $errorCode = JobStatus::ERROR_DIED, $errorMessage = $error['message'], $isRunning = false, $isLocked = false);
			} else {
				Utils::updateStatusJobError($params[0], getmypid(), $errorCode = JobStatus::ERROR_DIED, $errorMessage = 'looks like job called exit()... could not detect fatal in shutdown listener. Last error: ' . $error['message'], $isRunning = false, $isLocked = false);
			}
			self::$callback = false;
		}
	}

}