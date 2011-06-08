<?php
/* @var $model Foomo\Log\Plot\Plotter */
?>plot \
<?

$sessionCount = count($model->sessions);
for($i=0;$i<$sessionCount;$i++):
	$sessionId = $model->sessions[$i];
?>
	"<?= $model->getSessionFileName($sessionId) ?>" using 1:2:5 notitle with points lt 1 pt 4 ps 1 lc rgb variable, \
	"<?= $model->getSessionFileName($sessionId) ?>" using 1:2:(0):(-$3) notitle  with vectors nohead, \
	"<?= $model->getSessionFileName($sessionId) ?>" using 1:2:5 notitle with points lt 1 pt 2 ps 1 lc rgb variable, \
	"<?= $model->getSessionFileName($sessionId) ?>" using 1:2 notitle with lines, \
	"<?= $model->getSessionFileName($sessionId) ?>" using 1:2:6 notitle with labels <?= ($i < count($model->sessions) -1)?', \\'.PHP_EOL : PHP_EOL ?>
<? endfor; ?>
