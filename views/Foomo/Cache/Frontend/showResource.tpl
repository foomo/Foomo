<?php
/* @var $view Foomo\MVC\View */
/* @var $model Foomo\Cache\Frontend\Model */
?>
<?= $view->partial('header') ?>

<h2>Resource viewer</h2>
<?= $view->partial('menu') ?>
Resource id: <?= $model->currentResource->id ?>

<br>
Resource name: <?= $model->currentResource->name ?>
<br>

	
	<ul>
		
		<li>Source class: <?= $model->currentResource->sourceClass ?></li>
		<li>Source method: <?= $model->currentResource->sourceMethod ?></li>
		
		<? if ($model->currentResource->sourceStatic == 0): ?>
				<li> Static call: FALSE (called on object)
		<? else: ?>
				<li> Static call: TRUE (called on class)
		<? endif; ?>

		<br>
		<? if ($model->currentResource->status != 0): ?>
				<li> Status: VALID</li>
		<? else: ?>
				<li> Status: INVALID</li>
		<? endif; ?>

		<li> Creation time <?= date('Y-m-d H:i:s', $model->currentResource->creationTime) ?></li>
		<? if ($model->currentResource->expirationTime != 0): ?>
			<li> Expiration time <?= date('Y-m-d d.m.Y H:i:s', $model->currentResource->expirationTime) ?></li>
		<? else: ?>
				<li> Expiration time never</li>
		<? endif; ?>

		<? if ($model->currentResource->expirationTimeFast != 0): ?>
				<li> Expiration time <?= date('Y-m-d H:i:s', $model->currentResource->expirationTimeFast) ?></li>
		<? else: ?>
				<li> Expiration time never</li>
		<? endif; ?>

		<li>Queryable cache hits <?= $model->currentResource->hits ?></li>

		<li>Invalidation policy <?= $model->currentResource->invalidationPolicy ?></li>

	</ul>
	Resource value

	<?= var_dump($model->currentResource->value) ?>
	<br>


	

	<?= $view->partial('resourceProperties', array("resource" => $model->currentResource))?>
