<?php

$lockName = $_GET['lockName'];
$sleep = 0;
if (isset($_GET['sleep'])) {
	$sleep = intval($_GET['sleep']);
}

if ($sleep == 0) {
	$lockObtained = \Foomo\Lock::lockResource($lockName, $blocking = false);
	echo $lockObtained ? 'true' : 'false';
	exit;
} else {
	
	$lockObtained = \Foomo\Lock::lockResource($lockName, $blocking = false, 'description');
	//trigger_error('lock obtained ' . ($lockObtained ? 'true' : 'false'));
	echo $lockObtained ? 'true' : 'false';
	$info = \Foomo\Lock::getLockInfo($lockName);
	trigger_error('data ' . $info['lockData']);
	trigger_error('pid ' . $info['pid']);
	
	
	sleep($sleep);
	$release = \Foomo\Lock::releaseResource($lockName);
	trigger_error('lock released ' . ($release ? 'true' : 'false'));
	
	exit;
	
}