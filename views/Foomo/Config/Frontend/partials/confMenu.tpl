<?

/* @var $view Foomo\MVC\View */
/* @var $model Foomo\Config\Frontend\Model */

if($model->showConfigModule) {
	$parms = array('module' => $model->showConfigModule, 'name' => $model->showConfigDomain, 'domain' => $model->showConfigSubDomain);
} else if($model->currentConfigModule) {
	$parms = array('module' => $model->currentConfigModule, 'name' => $model->currentConfigDomain, 'domain' => $model->currentConfigSubDomain);
}
?>
<?= $view->partial('confHeader', $parms) ?>
<?= $view->link('show', 'showConf', $parms, 'show conf') ?> |
<?= $view->link('edit', 'confEditor', $parms, 'edit conf') ?> |
<?= $view->link('delete', 'deleteConf', $parms, 'delete conf') ?>
