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
class Utils {

	public static function collectJobs() {
		$jobLists = \Foomo\AutoLoader::getClassesByInterface(__NAMESPACE__ . '\\JoblistInterface');
		$jobs = array();
		foreach ($jobLists as $joblistClassName) {
			$moduleName = \Foomo\Modules\Manager::getClassModule($joblistClassName);
			if (!isset($jobs[$moduleName])) {
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
	 * @return JobStatus
	 */
	public static function getStatus(AbstractJob $job) {
		$jobId = $job->getId();
		return self::getPersistedStatus($jobId);
	}

	public static function getExecutionSecret() {
		$executionSecretFilename = self::getExecutionSecretFilename();
		if (!file_exists($executionSecretFilename)) {
			self::makeExecutionSecret();
		}
		return file_get_contents($executionSecretFilename);
	}

	private static function makeExecutionSecret() {
		file_put_contents(self::getExecutionSecretFilename(), uniqid('', true) . '-' . uniqid('', true) . '-' . uniqid('', true));
	}

	/**
	 * 
	 * @return string
	 */
	private static function getExecutionSecretFilename() {
		return \Foomo\Config::getVarDir(\Foomo\Module::NAME) . DIRECTORY_SEPARATOR . 'job-execution-secret.txt';
	}

	public static function getCrontab() {
		$crontab = new Crontab(self::collectJobs(), self::getExecutionSecret());
		return $crontab->getCrontab();
	}

	public static function installCrontab() {
		$crontab = new Crontab(self::collectJobs(), self::getExecutionSecret());
		$crontab->installCrontab();
	}

	/**
	 * 
	 * @param type $jobId
	 * @return AbstractJob
	 */
	public function getJobById($jobId) {
		foreach (\Foomo\Jobs\Utils::collectJobs() as $module => $jobs) {
			foreach ($jobs as $job) {
				if ($job->getId() == $jobId) {
					return $job;
				}
			}
		}
		return null;
	}

	/**
	 * 
	 * @param string $jobId
	 * @param integer $pid
	 * @param boolean $isLocked
	 */
	public static function updateStatusJobStart($jobId, $pid, $isLocked) {
		$status = self::getPersistedStatus($jobId);
		$fileName = self::getJobStatusFile($jobId);
		$status->status = JobStatus::STATUS_RUNNING;
		$status->isLocked = $isLocked;
		$status->pid = $pid;
		$status->startTime = time();
		$status->endTime = false;
		$status->errorCode = '';
		$status->errorMessage = '';
		self::persistStatus($fileName, $status);
	}

	/**
	 * 
	 * @param string $jobId
	 * @param integer $pid
	 */
	public static function updateStatusJobDone($jobId, $pid) {
		$status = self::getPersistedStatus($jobId);
		$fileName = self::getJobStatusFile($jobId);
		$status->status = JobStatus::STATUS_NOT_RUNNING;
		$status->isLocked = false;
		$status->pid = $pid;
		//$status->startTime = time();
		$status->endTime = time();
		$status->errorCode = '';
		$status->errorMessage = '';
		self::persistStatus($fileName, $status);
	}

	/**
	 * 
	 * @param string $jobId
	 * @param integer $pid
	 * @param string $errorCode one of self:ERROR_
	 * @param string $errorMessage 
	 * @param string $isRunning one of self::STATUS_
	 * @param boolean $isLocked
	 */
	public static function updateStatusJobError($jobId, $pid, $errorCode, $errorMessage, $isRunning = false, $isLocked = false) {
		$status = self::getPersistedStatus($jobId);
		$fileName = self::getJobStatusFile($jobId);
		$status->status = $isRunning ? JobStatus::STATUS_RUNNING : JobStatus::STATUS_NOT_RUNNING;
		$status->isLocked = $isLocked;
		$status->pid = $pid;
		//$status->startTime = time();
		$status->endTime = time();
		$status->errorCode = $errorCode;
		$status->errorMessage = $errorMessage;
		self::persistStatus($fileName, $status);
	}

	private static function getPersistedStatus($jobId) {
		$fileName = self::getJobStatusFile($jobId);
		if (file_exists($fileName)) {
			$contents = unserialize(file_get_contents($fileName));
		} else {
			$contents = new JobStatus();
		}
		return $contents;
	}

	private static function getJobStatusFile($jobId) {
		return \Foomo\Config::getVarDir(\Foomo\Module::NAME) . DIRECTORY_SEPARATOR . $jobId . '.dat';
	}

	private static function persistStatus($fileName, $status) {
		file_put_contents($fileName, serialize($status));
	}

}