<?php
/* @var $view Foomo\MVC\View */
/* @var $model Foomo\Cache\Frontend\Model */

$resource = $model->getEmptyResource($resourceName);

?>
<div id="resourceProps">
	<p>Resource property type definitions</p>
	<? foreach ($resource->getPropertyDefinitions() as $propName => $propertyDef): ?>
		<pre><?= $view->escape($propName) ?> : <?= $view->escape($propertyDef->type) ?></pre>
	<? endforeach; ?>
</div>