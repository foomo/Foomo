<?= $view->partial('header') ?>
<?= $view->partial('menu') ?>
<?= Foomo\MVC::run('Foomo\Cache\Frontend') ?>
<?= $view->partial('footer') ?>