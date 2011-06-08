<p>Old config <?= date('Y-m-d H:i:s', $model->oldConfig->timestamp) ?></p>
<?= $view->partial('confHeader', array('name' => $model->oldConfig->name, 'module' => $model->oldConfig->module, 'domain' => $model->oldConfig->domain)) ?>
<p>
	<?= $view->link('restore', 'restoreOldConf', array($model->oldConfig->id), 'restore this old config') ?> | 
	<?= $view->link('delete', 'deleteOldConf', array($model->oldConfig->id), 'restore this old config') ?>
</p>
<pre>
<?= $view->escape(file_get_contents($model->oldConfig->filename)) ?>
</pre>
