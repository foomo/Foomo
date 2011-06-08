#!/bin/bash
# auto generated file from <?= $_SERVER['SERVER_NAME'] ?> at <?= date('Y-m-d H:i:s') ?>


export FOOMO_RUN_MODE=<?= Foomo\Config::getMode() ?>

<? foreach(array('FOOMO_CACHE_QUERYABLE', 'FOOMO_CACHE_FAST') as $cacheEnvVar): ?>
export <?= $cacheEnvVar ?>=<?= escapeshellarg($_SERVER[$cacheEnvVar]) ?>

<? endforeach; ?>

php -d auto_prepend_file=<?= escapeshellarg(\Foomo\ROOT . DIRECTORY_SEPARATOR . 'bootstrap' . DIRECTORY_SEPARATOR . 'foomo.inc.php') ?> $*
