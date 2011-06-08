<?php
// How to use Foomo\Timer


// add markers some where in the middle of your code
Foomo\Timer::addMarker('settings are done');


// When you are done
// get the stats to process them
$plainTextStats = Foomo\Timer::getStats();

// or log them to the error_log
Foomo\Timer::writeStatsToFile();

// or add them to a file of your choice
Foomo\Timer::writeStatsToFile(Foomo\Config::getLogDir('moduleFoo') . DIRECTORY_SEPARATOR . 'myStats');


// you can also use it (in addition) to log the performance of a script
// this will log the performance for each call with E_USER_NOTICE and
// it will log with E_USER_WARNING if $maxTime was exceeded

$maxTime = '0.5'; // maximal execution time in seconds
Foomo\Timer::logTime($maxTime = '0.5');
