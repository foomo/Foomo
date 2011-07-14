<?php
/* @var $view Foomo\MVC\View */
/* @var $model Foomo\Cache\Frontend\Model */
?>


<?= $view->partial('menu') ?>

<div id="appContent">
	
	<div class="rightBox">
		<?= $view->link('Refresh dependency model', 'refreshDependencyModelAll', array(), array('title' => 'Refreshes the cached dependency model','class' => 'linkButtonYellow')) ?>
	</div>

	<? foreach($model->getResourceList() as $moduleName => $info): ?>
	<? if(count($info['resources'])>0): ?>
	
		<h2><?= $moduleName ?></h2>

		<? foreach($info['resources'] as $resourceName): ?>

			<? if( count($model->getDependencies($resourceName)) >0 ): ?>

				<div class="toggleBox">
					<div class="toogleButton">
						<div class="toggleOpenIcon">+</div>
						<div class="toggleOpenContent"><?= $view->link($resourceName, 'showCachedItems', array($resourceName)) ?></div>
					</div>
					<div class="toggleContent" style="margin-left: 40px;">
						<?= $view->partial('dependencyTree', array('resources' => $model->getDependencies($resourceName) )) ?>
					</div>
				</div>

			<? else: ?>

				<div class="greyBox">
					<div class="innerBox" style="margin: 5px 5px 5px 42px;">
						<b><?= $view->link($resourceName, 'showCachedItems', array($resourceName)) ?></b>
					</div>
				</div>

			<? endif; ?>

		<? endforeach; ?>
			
	<? endif; ?>	
	<? endforeach; ?>
</div>

