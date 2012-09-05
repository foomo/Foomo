<?php
$job = $model->getJob();
$status = $model->getJobStatus();
?><h1>Error message</h1>

<h2>Error code:</h2> <?= $status->errorCode ?>

<h2>Error message:</h2>
<p>
	<?= $view->escape($status->errorMessage) ?>
</p>

<h2>Error time:</h2>

<p>
	<?= date('y-m-d H:i:s', $status->endTime) ?>
</p>
