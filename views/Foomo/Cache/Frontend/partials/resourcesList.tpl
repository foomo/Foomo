<ul>
	<?
		foreach ($resources as $resource):
			 $valid = ($resource->status == \Foomo\Cache\CacheResource::STATUS_VALID) || ($resource->expirationTime < \time());
			 $class = $valid?'valid':'invalid';
			 $msg = $valid?'valid':'invalid';
	?>
		<div class="<?= $class ?>">
			<li><?= $view->escape($resource->name) ?> (<?= $msg ?> <?= $view->link($resource->id, 'showResource', array($resource->name, $resource->id), array('title' => "View cached")) ?>
				<ul>
					<li><?= $view->link('PREVIEW INVALIDATION', 'actionPreviewRebuildId', array($resource->name, $resource->id), array('title' => 'Preview invalidation of cached esource')) ?></li>
					<li><?= $view->link('REBUILD', 'actionRebuildId', array($resource->name, $resource->id, \Foomo\Cache\Invalidator::POLICY_INSTANT_REBUILD), array('title' => 'Re-create the cached object. Creation time is set to NOW. Expiration times for fast and queryable caches are updated. Dependent objects are also recreated. ')) ?></li>
					<li><?= $view->link('INVALIDATE', 'actionRebuildId', array($resource->name, $resource->id, \Foomo\Cache\Invalidator::POLICY_INVALIDATE), array('title' => 'Set cached object STATUS property to INVALID, resulting in re-creation of the object when next requested from the cache. Dependent objects are also invalidate.')) ?></li>
					<li><?= $view->link('DELETE', 'actionRebuildId', array($resource->name, $resource->id, \Foomo\Cache\Invalidator::POLICY_DELETE), array('title' => 'Delete cached object from fast and queryable caches. Dependent objects are also deleted.')) ?></li>
				</ul>
			</li>
			<ul>
				<?
					// @todo is it good to manipulate the model from here ?!
					$model->resourcePropertiesCurrentResource = $resource;
					echo $view->partial('resourceProperties',array('resource' => $resource));
				?>
			</ul>
		</div>
	<? endforeach; ?>
</ul>