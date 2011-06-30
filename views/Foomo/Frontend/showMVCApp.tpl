<?= $view->partial('header') ?>
<?= $view->partial('menu') ?>
<div id="main">
	<?= Foomo\MVC::run($model->currentModuleApp, $view->url('showMVCApp'), true); ?>
</div>
<?= $view->partial('footer') ?>

