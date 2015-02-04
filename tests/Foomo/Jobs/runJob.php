<?php

if (isset($_GET['job'])) {
	$jobName = $_GET['job'];
	$job = call_user_func_array([ "Foomo\\Jobs\\Mock\\" . $jobName, "create"], []);
	\Foomo\Jobs\Runner::runAJob($job);
}

