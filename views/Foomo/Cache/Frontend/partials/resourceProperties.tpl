<?php
/* @var $view Foomo\MVC\View */
/* @var $model Foomo\Cache\Frontend\Model */
/* @var $resource Cache */
$propDefs = $resource->getPropertyDefinitions();
if(count($propDefs) == 0) {
	return;
}
?><ul><li>Properties:

<?	foreach ($propDefs as $propName => $propValue): ?>
		<? if(isset($resource->properties[$propName])): ?>
		<pre><?= $view->escape($propName) ?> = <?= $view->escape(is_string($resource->properties[$propName])?$resource->properties[$propName]:json_encode($resource->properties[$propName])) ?></pre>
		<? endif; ?>
<? endforeach; ?>
<pre></pre></li></ul>
