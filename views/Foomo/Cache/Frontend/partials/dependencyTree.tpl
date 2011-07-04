<?php
/* @var $view Foomo\MVC\View */
/* @var $model Foomo\Cache\Frontend\Model */
?>
<? if(count($resources) > 0): ?>

<!--
<div class="toggleBox">
	<div class="toogleButton">
		<div class="toggleOpenIcon">+</div>
		<div class="toggleOpenContent">sers </div>
	</div>
	<div class="toggleContent">
				

				
	</div>
</div>-->

		<ul>
		<? foreach($resources as $resourceName): ?>
			<li>
				<? $model->addedResources[] = $resourceName; ?>
				<?= $view->link($resourceName, 'actionShowCachedItems', array($resourceName), array('title' => "Show cached resources.")) ?>
				<?= $view->partial('dependencyTree', array('resources' => $model->getDependencies($resourceName))) ?>
			</li>
		<? endforeach; ?>
		</ul>

<? endif; ?>