<?php
/* @var $view Foomo\MVC\View */
/* @var $model Foomo\Cache\Frontend\Model */
?>
<?= $view->partial('header') ?>

<?= $view->partial('menu') ?>

<h2>Resources and dependencies</h2>
<?= $view->partial('dependencyTree', array('resources' => $model->getToplevelResources())) ?>
<?= $view->partial('dependencyList', array('resources' => $model->getOrphanToplevelResources())) ?>

<h2>Cacheable resources by module</h2>
<? foreach($model->getResourceList() as $moduleName => $info): ?>
	<? if(count($info['resources'])>0): ?>
		<h2><?= $moduleName ?></h2>
		<ul>
			<? foreach($info['resources'] as $resourceName): ?>
				<li>
					<?= $view->link($resourceName, 'showCachedItems', array($resourceName),"Show cached resources") ?>
				</li>
			<? endforeach; ?>
		</ul>
	<? endif; ?>
<? endforeach; ?>


