<?php
/* @var $view Foomo\MVC\View */
/* @var $model Foomo\Cache\Frontend\Model */
?>

<?= $view->partial('menu') ?>
<h2>List of rebuilt resources when issued for : <?= $view->escape($model->currentResourceName) ?> <?= $view->escape($model->currentResourceId) ?></h2>
<ul>
	<? foreach ($model->currentInvalidationList as $resource): ?>
				<li><?= $resource->name?> <?= $view->link($resource->id, 'showResource', array($resource->name, $resource->id)) ?></li>
				<?= $view->partial('resourceProperties',array('resource' => $resource)); ?>
	<? endforeach; ?>
</ul>

<h2>---</h2>
