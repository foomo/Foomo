<?php
/* @var $view Foomo\MVC\View */
/* @var $model Foomo\Cache\Frontend\Model */
?>

<?= $view->partial('menu') ?>

<h2> Warning !!!! </h2>
<div class="whiteBox">
	<div class="innerBox">

	<? if ($model->currentOperation == 'reset'): ?>

		<h3>Cache RESET will result in irreversible deletion of ALL cached resources and recreation of storage structures, resulting in possibly lengthy resource re-creation when next requested. Be sure you know what you are doing!</h3>
		<br>
		<h3>Are you sure you want to continue?</h3>
		<br>
		<?= $view->link("NO, Please let me out", 'actionDefault', array(), array('class' => 'linkButtonYellow')) ?>
		<?= $view->link("YES, proceed", 'actionSetUpCacheStructure', array(), array('class' => 'linkButtonRed')) ?>

	<? elseif ($model->currentOperation == 'populateFastCache'): ?>
			
		<h3>This operation populates the fast cache with persisted resources with from the queryable cache. This can take some time!</h3>
		<br>
		<h3>Are you sure you want to continue?</h3>
		<br>
		<?= $view->link("NO, Please let me out", 'actionDefault', array(), array('class' => 'linkButtonYellow')) ?>
		<?= $view->link("YES, proceed", 'actionPopulateFastCache', array(), array('class' => 'linkButtonRed')) ?>


	<? else: ?>

		<h2>We should never come here!!!</h2>

	<? endif; ?>
		
		<br>
		<br>
	</div>
</div>


