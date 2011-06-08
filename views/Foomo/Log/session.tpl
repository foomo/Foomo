<?php

/* @var $session \Foomo\Log\UserSession */
$session = $model['session'];
/* @var $printer \Foomo\Log\Printer */
$printer = $model['printer'];
?>

Session Id : <?= $session->sessionId ?>

Speed      : <?= $session->getSpeedEstimate() ?>


Errors

<? foreach($session->errors as $name => $count): ?>
    <?= str_pad($name, 10) ?>: <?= $count ?>

<? endforeach; ?>


Calls

<? foreach($session->calls as $callFile => $callCount): ?>
    <?= str_pad($callCount, 5) ?> : <?= $callFile ?>

<? endforeach; ?>

