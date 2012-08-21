<?php

/* @var $job Foomo\Jobs\AbstractJob */
/* @var $view Foomo\MVC\View */

?>
<tr>
	<td colspan="6"><?= $module . ' ' . get_class($job) ?></td>
</tr>
<tr>
	<td><?= $view->escape($job->getExecutionRule()) ?></td>
	<td><?= $view->escape($job->getDescription()) ?></td>
	<td><?= $job->getLock()?'yes':'no' ?></td>
	<td><?= $view->escape($job->getMaxExecutionTime()) ?></td>
	<td><?= $view->escape($job->getMemoryLimit()) ?></td>
	<td><?= \Foomo\Jobs\Utils::getStatus($job)->status ?></td>
</tr>
