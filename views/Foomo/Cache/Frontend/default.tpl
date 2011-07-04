<?php
/* @var $view Foomo\MVC\View */
/* @var $model Foomo\Cache\Frontend\Model */
?>
<div id="fullContent">
	<?= $view->partial('header') ?>

	<?= $view->partial('menu') ?>
	<h2>Resources and dependencies</h2>
	<?= $view->partial('dependencyTree', array('resources' => $model->getToplevelResources())) ?>
	<hr>
	<?= $view->partial('dependencyList', array('resources' => $model->getOrphanToplevelResources())) ?>

	<h2>Cacheable resources by module</h2>
	<? foreach($model->getResourceList() as $moduleName => $info): ?>
		<? if(count($info['resources'])>0): ?>
			<h2><?= $moduleName ?> a </h2>
			<ul>
				<? foreach($info['resources'] as $resourceName): ?>
					<li>
						<?= $view->link($resourceName, 'showCachedItems', array($resourceName), array('title' => "Show cached resources")) ?>
					</li>
				<? endforeach; ?>
			</ul>
			
		<? endif; ?>
			
	<? endforeach; ?>

</div>
