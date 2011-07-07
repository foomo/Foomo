<?php
/* @var $view Foomo\MVC\View */
/* @var $model Foomo\Cache\Frontend\Model */
?>

<?= $view->partial('menu') ?>

<h2> Warning !!!! </h2>
<div class="whiteBox">
	<div class="innerBox">
		<h3>Cache SETUP will result in irreversible deletion of ALL cached resources called <?= $model->currentResourceName ?> and setup of its structure (table), resulting in possibly lengthy resource re-creation when next requested. Cache consistence might get compromised. Be sure you know what you are doing!</h3>
		<br>
		<h3>Are you sure you want to continue?</h3>
		<br>
		<?= $view->link("NO, Please let me out", 'default', array(), array('class' => 'linkButtonYellow backButton')) ?> 
		<?= $view->link("YES, proceed", 'setUpOne', array($model->currentResourceName, true), array('class' => 'linkButtonRed overlay')) ?>
		<br>
		<br>
	</div>
</div>