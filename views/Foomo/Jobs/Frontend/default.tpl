<?php

/* @var $model Foomo\Jobs\Frontend\Model */
/* @var $view Foomo\MVC\View */

?><nav id="menuSub">
	<ul>
		<li><?= $view->partial('menuButton', array('url' => 'previewCrontab', 'name' => 'preview crontab' ), 'Foomo\Frontend') ?></li>
		<li><?= $view->partial('menuButton', array('url' => 'installCrontab', 'name' => 'install crontab' ), 'Foomo\Frontend') ?></li>
	</ul>
</nav>

<? if(!isset($_SERVER['HTTPS'])): ?>
<div class="errorContainer">You are not using https to connect to your server => all cronjobs well be called over http when using curl</div>
<? endif; ?>
<table>
	<thead>
		<tr>
			<th>
				execution rule
			</th>
			<th>
				description
			</th>
			<th>
				locks
			</th>
			<th>
				max execution time
			</th>
			<th>
				memory limit
			</th>
			<th>
				status
			</th>
		</tr>
	</thead>
	<tbody>
		<? foreach(\Foomo\Jobs\Utils::collectJobs() as $module => $jobs): ?>
		<? foreach($jobs as $job): ?>
			<?= $view->partial('job', array('job' => $job, 'module' => $module)) ?>
		<? endforeach; ?>
		<? endforeach; ?>
	</tbody>
</table>
