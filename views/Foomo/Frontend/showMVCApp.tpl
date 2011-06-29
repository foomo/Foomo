<?= $view->partial('header') ?>
<?= $view->partial('menu') ?>
<div id="main">
	<?= Foomo\MVC::run($model->currentModuleApp); ?>
</div>
<?= $view->partial('footer') ?>

