<?
$showOldConfId = 'old-' . $oldConfig->id;
?>
<ul id="ctrlButtons" >
	<li><?= $view->partial('buttonYellow', array('url' => '', 'name' => 'Show', 'js' => 'onclick="$(\'#'. $showOldConfId .'\').toggle(300)"'  ), 'Foomo\Frontend') ?></li>
	<li><?= $view->partial('buttonYellow', array('url' => 'restoreOldConf', 'name' => 'Restore' , 'parameters' => array($oldConfig->id)), 'Foomo\Frontend') ?></li>
	<li><?= $view->partial('buttonYellow', array('url' => 'deleteOldConf', 'name' => 'Delete' , 'parameters' => array($oldConfig->id)), 'Foomo\Frontend') ?></li>
</ul>

<div class="show" id="<?= $showOldConfId ?>" style="display:none">
	<pre><?= $view->escape(file_get_contents($oldConfig->filename)) ?></pre>
</div>
