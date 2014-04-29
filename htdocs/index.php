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

Timer::start($topic = 'toolbox ' . $_SERVER['REQUEST_URI']);
ob_start();

Frontend::setUpToolbox();

$bootstrapTime = toolbox_time(microtime(true) - \Foomo\SYSTEM_START_MICRO_TIME);

$start = microtime(true);

Session::lockAndLoadIfEnabled();

$html = MVC::run('Foomo\Frontend');

Timer::stop($topic);

$runTime = toolbox_time(microtime(true) - $start);

echo str_replace(
	array('%foomoRunTimeBoot%', '%foomoRunTimeRun%'),
	array($bootstrapTime, $runTime),
	$html
);


Timer::writeStatsToFile();