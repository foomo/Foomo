<?php
/* @var $view Foomo\MVC\View */
/* @var $model Foomo\Cache\Frontend\Model */
?>
<? if(count($resources) > 0): ?>
<ul>
<? foreach($resources as $resourceName): ?>
	<li>
		<?= $view->link($resourceName, 'actionShowCachedItems', array($resourceName), array('title' => "Show cached resources.")) ?>

<? endforeach; ?>
</ul>
<? endif; ?>