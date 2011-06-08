<?php
/* @var $view Foomo\MVC\View */
/* @var $model Foomo\Cache\Frontend\Model */
?>
<? if(count($resources) > 0): ?>
<ul>
<? foreach($resources as $resourceName): ?>
	<li>
		<? $model->addedResources[] = $resourceName; ?>
		<?= $view->link($resourceName, 'actionShowCachedItems', array($resourceName), "Show cached resources.") ?>
		<?= $view->partial('dependencyTree', array('resources' => $model->getDependencies($resourceName))) ?>
	</li>
<? endforeach; ?>
</ul>
<? endif; ?>