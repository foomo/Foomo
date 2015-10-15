<?php

if(extension_loaded('newrelic')) {
	newrelic_background_job(true);
	newrelic_ignore_apdex();
	newrelic_ignore_transaction();
}

$parts = explode('/', $_SERVER['REQUEST_URI']);
if(count($parts)>0) {
	\Foomo\Jobs\Runner::runJob(array_pop($parts));
}


