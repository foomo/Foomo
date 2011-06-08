<?php
/* @var $view Foomo\MVC\View */
/* @var $model Foomo\Cache\Frontend\Model */
?>
<?= $view->partial('header') ?>

<?= $view->partial('menu') ?>

<h2> Warning !!!! </h2>

<? if ($model->currentOperation == 'reset'): ?>
	<h2>Cache RESET will result in irreversible deletion of ALL cached resources and recreation of storage structures, resulting in possibly lengthy resource re-creation when next requested. Be sure you know what you are doing!</h2>

	<br>

	<h2>Are you sure you want to continue?</h2>

	<?= $view->link("[YES, proceed]", 'actionSetUpCacheStructure') ?>

	<?= $view->link("[NO, Please let me out]", 'actionDefault') ?>
<? elseif ($model->currentOperation == 'setupone'): ?>
		<h2>Cache SETUP will result in irreversible deletion of ALL cached resources called <?= $model->currentResourceName ?> and setup of its structure (table), resulting in possibly lengthy resource re-creation when next requested. Cache consistence might get compromised. Be sure you know what you are doing!</h2>

		<br>

		<h2>Are you sure you want to continue?</h2>

<?= $view->link("[YES, proceed]", 'setupone', array($model->currentResourceName)) ?>

<?= $view->link("[NO, Please let me out]", 'default') ?>

<? elseif ($model->currentOperation == 'setup'): ?>
			<h2>Cache SETUP will result in irreversible deletion of ALL cached resources and recreation of storage structures, resulting in possibly lengthy resource re-creation when next requested. Be sure you know what you are doing!</h2>

			<br>

			<h2>Are you sure you want to continue?</h2>

<?= $view->link("[YES, proceed]", 'actionSetUpCacheStructure') ?>

<?= $view->link("[NO, Please let me out]", 'actionDefault') ?>
<? elseif ($model->currentOperation == 'populateFastCache'): ?>
			<h2>This operation populates the fast cache with persisted resources with from the queryable cache. This can take some time!</h2>

			<br>

			<h2>Are you sure you want to continue?</h2>

<?= $view->link("[YES, proceed]", 'actionPopulateFastCache') ?>

<?= $view->link("[NO, Please let me out]", 'actionDefault') ?>



<? else: ?>

				<h2>We should never come here!!!</h2>

<? endif; ?>



