<?
/* @var $model Foomo\Config\Frontend\Model */
/* @var $view Foomo\MVC\View */
?>
<p>Current config</p>
<p>
	<?= ($model->showConfigComment)?$view->escape($model->showConfigComment) . ' | ' : '' ?>
	<?= $view->partial('confMenu') ?>
</p>
<pre>
<? if(Foomo\Config::confExists($model->showConfigModule, $model->showConfigDomain, $model->showConfigSubDomain)): ?>
<?= $view->escape( Foomo\Config::getConfOriginalYaml($model->showConfigModule, $model->showConfigDomain, $model->showConfigSubDomain)) ?>

<?= var_dump(Foomo\Config::getConf($model->showConfigModule, $model->showConfigDomain, $model->showConfigSubDomain)); ?>
<? else: ?>
 conf does ot exist
<? endif; ?>
</pre>