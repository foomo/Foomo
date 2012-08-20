<?php

$parts = explode('/', $_SERVER['REQUEST_URI']);
if(count($parts)>0) {
	\Foomo\Jobs\Runner::runJob(array_pop($parts));
}


