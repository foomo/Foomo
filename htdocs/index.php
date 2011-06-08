<?php

namespace Foomo;

/**
 *
 * @internal
 *
 * @param float $microTime
 *
 * @return float
 */
function toolbox_time($microTime)
{
	return round($microTime * 1000,2);
}

ob_start();

Frontend::setUpToolbox();

$bootstrapTime = toolbox_time(microtime(true) - \Foomo\SYSTEM_START_MICRO_TIME);

$start = microtime(true);

Session::lockAndLoadIfEnabled();

$html = MVC::run('Foomo\Frontend');

$runTime = toolbox_time(microtime(true) - $start);

echo str_replace(
	array('%foomoRunTimeBoot%', '%foomoRunTimeRun%'),
	array($bootstrapTime, $runTime),
	$html
);