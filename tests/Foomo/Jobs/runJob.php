<?php

if (isset($_GET['job'])) {
	$jobName = $_GET['job'];

	if ($jobName == 'SleeperJob') {
		\Foomo\Jobs\Runner::runAJob(\Foomo\Jobs\Mock\SleeperJob::create());
	}

	if ($jobName == 'DierJob') {
		\Foomo\Jobs\Runner::runAJob(\Foomo\Jobs\Mock\DierJob::create());
	}

	if ($jobName == 'ExiterJob') {
		\Foomo\Jobs\Runner::runAJob(\Foomo\Jobs\Mock\ExiterJob::create());
	}

	if ($jobName == 'DieWhileWorkingJob') {
		\Foomo\Jobs\Runner::runAJob(\Foomo\Jobs\Mock\DieWhileWorkingJob::create());
	}
}

