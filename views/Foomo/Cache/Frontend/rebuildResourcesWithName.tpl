<?php
/* @var $view Foomo\MVC\View */
/* @var $model Foomo\Cache\Frontend\Model */
?>

<h2>Rebuilt cached resources when issued for:  <?= $view->escape($model->currentResourceName) ?></h2>
<?= $view->partial('menu') ?>


<ul>
	<? foreach ($model->currentInvalidationList as $resource): ?>
				<li><?= $resource->name ?> <?= $view->link($resource->id, 'showResource', array($resource->name, $resource->id)) ?></li>
				<?= $view->partial('resourceProperties',array('resource' => $resource)); ?>
		<? endforeach; ?>
</ul>

<h2>---</h2>
