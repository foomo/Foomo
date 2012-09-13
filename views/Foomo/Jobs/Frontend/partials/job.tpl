<?php
/* @var $job Foomo\Jobs\AbstractJob */
/* @var $view Foomo\MVC\View */
?>
<tr>
	<td colspan="9"><?= $module . ' ' . get_class($job) ?></td>
</tr>
<tr style="<?= \Foomo\Jobs\Utils::getStatus($job)->isOk() ? 'color:black' : 'color:red' ?>">
	<td><?= $view->escape($job->getExecutionRule()) ?></td>
	<td><?= $view->escape($job->getDescription()) ?></td>
	<td><?= $job->getLock() ? 'yes' : 'no' ?></td>
	<td><?= $view->escape($job->getMaxExecutionTime()) ?></td>
	<td><?= $view->escape($job->getMemoryLimit()) ?></td>
	<td><?= \Foomo\Jobs\Utils::getStatus($job)->status ?></td>
	<td><a  href="<?= $view->url('actionStatusView', array($job->getId())) ?>"><?= \Foomo\Jobs\Utils::getStatus($job)->errorCode ?></a></td>
	<td><?= \Foomo\Jobs\Utils::getStatus($job)->startTime ? date('Y-m-d H:i:s', \Foomo\Jobs\Utils::getStatus($job)->startTime) : '' ?></td>
	<td><?= \Foomo\Jobs\Utils::getStatus($job)->endTime ? date('Y-m-d H:i:s', \Foomo\Jobs\Utils::getStatus($job)->endTime) : '' ?></td>

</tr>
