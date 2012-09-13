<?php

if(count($_SERVER['argv'])) {
	\Foomo\Jobs\Runner::runJob($_SERVER['argv'][1]);
} else {
	die('nothing to do - no job id given');
}