<?php
/* @var $view Foomo\MVC\View */
/* @var $model Foomo\Cache\Frontend\Model */
?>
<?= $view->partial('header') ?>

<?= $view->partial('menu') ?>

<h2> Warning !!!! </h2>
	<p>Cache SETUP will result in irreversible deletion of ALL cached resources called <?= $model->currentResourceName ?> recreation of its structure (table), resulting in possibly lengthy resource re-creation when next requested. Cache consistence might get compromised. Be sure you know what you are doing!</p>
	<p><i>You typically only want to do this during development, when you are changing the signature of a resource by changing its parameters.</i>
	<br>

<p>Are you sure you want to continue?</p>

<?= $view->link("[YES, proceed]", 'setUpOne', array($model->currentResourceName, 'true')) ?>

<?= $view->link("[NO, Please let me out]", 'default') ?>

