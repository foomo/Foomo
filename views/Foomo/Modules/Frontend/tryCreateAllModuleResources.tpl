<?= $view->partial('menu') ?>
<div id="appContent">
<p>Tried to create missing resources for all modules</p>
<pre>
<?= $view->escape($model->resourceCreationReport) ?>
</pre>
</div>