<?php
/* @var $model Foomo\Log\Plot\Plotter */
?>plot \
	"<?= $model->getAllDataFile() ?>" using 1:2:4 with points lt 1 pt 4 ps 1 lc rgb variable, \
	"<?= $model->getAllDataFile() ?>" using 1:2:(0):(-$3) with vectors nohead, \
	"<?= $model->getAllDataFile() ?>" using 1:2:5 with points lt 1 pt 2 ps 1 lc rgb variable, \
	"<?= $model->getAllDataFile() ?>" using 1:2:6 with labels

	