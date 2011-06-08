<?
/* @var $model Foomo\Config\Frontend\Model */
?>
<?= $view->partial('menu') ?>
<div id="appContent">
	<? if($model->currentConfigDomain): ?>
		<?= $view->partial('edit') ?>
	<? elseif($model->showConfigDomain): ?>
		<?= $view->partial('show') ?>
	<? elseif($model->oldConfig): ?>
		<?= $view->partial('old') ?>
	<? else: ?>
		<?= $view->partial('configList') ?>
	<? endif; ?>
</div>
