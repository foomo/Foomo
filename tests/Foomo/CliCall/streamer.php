<?php

$args = $_SERVER['argv'];
if(count($args) == 3) {
	function streamBytesTo($to, $numberOfBytes) {
		$sent = 0;
		$multiplier = 1024;
		while($sent < $numberOfBytes) {
			if($sent + $multiplier > $numberOfBytes) {
				$multiplier = $numberOfBytes - $sent;
			}
			fwrite($to, str_repeat('A', $multiplier));
			$sent += $multiplier;
		}
	}
	if($args[1] > 0) {
		streamBytesTo(STDOUT, $args[1]);
	}
	if($args[2] > 0) {
		streamBytesTo(STDERR, $args[2]);
	}
	exit(0);
} else {
	echo 'wtf';
	exit(1);
}
