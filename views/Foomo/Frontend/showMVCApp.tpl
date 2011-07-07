<div id="page">
	<div id="innerPage">
		<?= $view->partial('header') ?>
		<?= $view->partial('menu') ?>
		<div id="main">
			<?= Foomo\MVC::run($model->currentModuleApp, $view->url('showMVCApp'), true); ?>
		</div>
		<?= $view->partial('footer') ?>
	</div>
</div>
<?= $view->partial('overlay') ?>