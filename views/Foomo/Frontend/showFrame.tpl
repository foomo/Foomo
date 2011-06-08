<?= $view->partial('header') ?>
<?= $view->partial('menu') ?>
<iframe style="height:100%;width:100%;" src="/r/<?= $view->escape($model->currentFrameUrl) ?>"></iframe>
<?= $view->partial('footer') ?>