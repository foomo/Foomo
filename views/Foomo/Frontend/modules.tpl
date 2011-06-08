<?= $view->partial('header') ?>
<?= $view->partial('menu') ?>
<?= Foomo\MVC::run('Foomo\\Modules\\Frontend') ?>
<?= $view->partial('footer') ?>