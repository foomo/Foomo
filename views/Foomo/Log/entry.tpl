<?php


static $arrayFunc;

if(!isset($arrayFunc)) {
    $arrayFunc = function($array) {
        foreach($array as $prop => $value) {
            echo '    ' . $prop . ': ' . $value . PHP_EOL;
        }
    };
}
/* @var $entry \Foomo\Log\Entry */
$entry = $model['entry'];
/* @var $printer \Foomo\Log\Printer */
$printer = $model['printer'];

?>
ID                      : <?= $entry->id ?>

Process Execution start : <?= $entry->logTime ?>  (<?= date('Y-m-d H:i:s', $entry->logTime) ?>)
Measured runtime        : <?= $entry->runTime ?> s
Processing time         : <?= $entry->processingTime ?> s
HTTP STATUS CODE        : <?= $entry->httpStatus ?>

Script filename         : <?= $entry->scriptFilename ?>

Peak memory usage       : <?= $entry->peakMemoryUsage / 1048576 ?> MB
Session Id              : <?= $entry->sessionId ?>

Session age             : <?= $entry->sessionAge ?>

<? if(count($entry->transactions) > 0): ?>


Transactions:

<? foreach($entry->transactions as $transaction): ?>
    name    : <?= $transaction['name'] . (!empty($transaction['comment'])?$transaction['comment']:'') ?>
    status  : <?= $transaction['status'] ?>
<? if($transaction['status'] != 'open'): ?>
    runtime : <?= $transaction['stop'] - $transaction['start'] ?> s
<? endif; ?>

<? endforeach; ?>

<? endif; ?>
<? if(!is_null($downloadSpeed = $entry->getConnectionSpeed())): ?>
Connection speed        : <?= $downloadSpeed ?> bytes / s
<? endif; ?>


Recorded Environment:

<? call_user_func_array($arrayFunc, array($entry->serverVars)); ?>
<? if(count($entry->phpErrors)>0):

?>

Errors:

<? foreach($entry->phpErrors as $phpError): ?>
<?= \Foomo\Module::getView('Foomo\\Log\\Logger', 'error', $phpError)->render() ?>
<? endforeach; ?>
<? endif; ?>
<? if(count($entry->stopwatchEntries) > 0): ?>

Measured times:

<? foreach($entry->stopwatchEntries as $topic => $stopwatchEntries):
    //var_dump($topic, $stopwatchEntries);
    foreach($stopwatchEntries as $stopwatchEntry):
        if(isset($stopwatchEntry['nl'])) {
            $nl = $stopwatchEntry['nl'];
        } else {
            $nl = 0;
        }
        //var_dump($stopwatchEntry);
        echo
            '    ' .
            str_repeat('  ', $nl) .
            $topic . ' : ' .
            ($stopwatchEntry['stop']-$stopwatchEntry['start']) . ' s ' .
            (isset($stopwatchEntry['comment'])?'"'.$stopwatchEntry['comment'].'"':'') .
            (($nl>0)?' nl ' . $nl:'')
        ;
?>

<?    endforeach; ?>
<? endforeach; ?>
<? endif; ?>

Program execution markers:

<?
$lastMarkerTime = 0;
$markerTextimit = 50;
foreach($entry->markers as $marker):
    if(strlen($marker[1]) > $markerTextimit) {
        $markerText = substr($marker[1], 0, $markerTextimit -3) . '...';
    } else {
        $markerText = $marker[1];
    }
?>
    <?= str_pad($marker[1], $markerTextimit, '.') . ' : ' . ($marker[0] - $lastMarkerTime)?>

<?
$lastMarkerTime = $marker[0];
endforeach; ?><? if(count($entry->postVars)): ?>
Post variables:

<? call_user_func_array($arrayFunc, array($entry->postVars)); ?>
<? endif; ?>

<? if(count($entry->getVars)): ?>
Get variables:

<? call_user_func_array($arrayFunc, array($entry->getVars)); ?>
<? endif; ?>
<? if($entry->exception): ?>

Exception:

    code    : <?= $entry->exception->getCode() ?>

    message : <?= $entry->exception->getMessage() ?>

    file    : <?= $entry->exception->getFile() ?>

    line    : <?= $entry->exception->getLine() ?>

    trace   :
        <?=  implode(PHP_EOL . '        ', explode(PHP_EOL, $entry->exception->getTraceAsString())) ?>

<? endif; ?>