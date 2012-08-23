<?php

if (isset($_GET['job'])) {
	$jobName = $_GET['job'];

	if ($jobName == 'SleeperJob') {
		$jobs = \Foomo\Jobs\Utils::collectJobs();
		$executionSecret = \Foomo\Jobs\Utils::getExecutionSecret();
		\Foomo\Jobs\Runner::runJob(\Foomo\Jobs\Mock\SleeperJob::create()->getSecretId($executionSecret));
	}

	if ($jobName == 'DierJob') {
		$jobs = \Foomo\Jobs\Utils::collectJobs();
		$executionSecret = \Foomo\Jobs\Utils::getExecutionSecret();
		\Foomo\Jobs\Runner::runJob(\Foomo\Jobs\Mock\DierJob::create()->getSecretId($executionSecret));
	}

	if ($jobName == 'ExiterJob') {
		$jobs = \Foomo\Jobs\Utils::collectJobs();
		$executionSecret = \Foomo\Jobs\Utils::getExecutionSecret();
		\Foomo\Jobs\Runner::runJob(\Foomo\Jobs\Mock\ExiterJob::create()->getSecretId($executionSecret));
	}

	if ($jobName == 'DieInSleepJob') {
		$jobs = \Foomo\Jobs\Utils::collectJobs();
		$executionSecret = \Foomo\Jobs\Utils::getExecutionSecret();
		\Foomo\Jobs\Runner::runJob(\Foomo\Jobs\Mock\DieInSlepJob::create()->getSecretId($executionSecret));
	}
}

