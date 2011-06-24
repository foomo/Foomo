<?
$showOldConfId = 'old-' . $oldConfig->id;
?>
<ul>
	<li>
		<a onclick="$('#<?= $showOldConfId ?>').toggle()">Show</a>
	</li>
	<li>
		<?= $view->link('delete', 'deleteOldConf', array($oldConfig->id), 'delete') ?>
	</li>
	<li>
		<?= $view->link('restore', 'restoreOldConf', array($oldConfig->id), 'restore') ?>
	</li>
</ul>
<div id="<?= $showOldConfId ?>" style="display:none">
	<pre><?= $view->escape(file_get_contents($oldConfig->filename)) ?></pre>
</div>
