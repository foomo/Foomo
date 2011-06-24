<?= $view->partial('header') ?>
<?= $view->partial('menu') ?>
<div id="main">
	<?= Foomo\MVC::run('Foomo\\Modules\\Frontend') ?>
</div>
<?= $view->partial('footer') ?>