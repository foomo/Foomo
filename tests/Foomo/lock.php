<?php

$lockName = $_GET['lockName'];
$sleep = 0;
if (isset($_GET['sleep'])) {
	$sleep = intval($_GET['sleep']);
}

if ($sleep == 0) {
	$lockObtained = \Foomo\Lock::lock($lockName, $blocking = false);
	echo $lockObtained ? 'true' : 'false';
	exit;
} else {
	
	$lockObtained = \Foomo\Lock::lock($lockName, $blocking = true);
	echo $lockObtained ? 'true' : 'false';
	sleep($sleep);
	\Foomo\Lock::release($lockName);
	exit;
	
}