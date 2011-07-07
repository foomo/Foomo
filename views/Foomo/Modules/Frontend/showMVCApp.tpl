<?= $view->partial('menu') ?>
<div id="appContent">
	<h2>Module - <?= $model->currentModuleApp ?></h2>
	<small><?= $view->link('<< back to overview', 'default', array(), array('title' => 'go back to the module overview')) ?></small>
	<?= Foomo\MVC::run($model->currentModuleApp); ?>
</div>

