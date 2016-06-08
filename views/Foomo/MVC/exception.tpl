<div>
<h1>An error occured</h1>
<? if(in_array(Foomo\Config::getMode(), array(Foomo\Config::MODE_DEVELOPMENT, Foomo\Config::MODE_TEST))): ?>
<pre>
Exception message:

<?= $view->escape($exception->getMessage()) ?>


Exception trace:

<?= $view->escape($exception->getTraceAsString()) ?>
</pre>
<? endif; ?>
</div>