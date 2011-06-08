<?= $view->partial('header') ?>
<?= $view->partial('menu') ?>
<?= Foomo\MVC::run('Foomo\\BasicAuth\\Frontend') ?>
<?= $view->partial('footer') ?>