
<?
	foreach ($resources as $resource):
		 $valid = ($resource->status == \Foomo\Cache\CacheResource::STATUS_VALID) || ($resource->expirationTime < \time());
		 $class = $valid?'valid':'invalid';
		 $msg = $valid?'valid':'invalid';
?>

<div class="toggleBox">
	<div class="toogleButton">
		<div class="toggleOpenIcon">+</div>
		<div class="toggleOpenContent"><?= $view->escape($resource->name) ?> (<?= $msg.' '.$resource->id?>)</div>
	</div>
	<div class="toggleContent">
		
		<div class="tabBox">
			<div class="tabNavi">
				<ul>
					<li class="selected">Info</li>
					<li>Value</li>
					<li>Properties</li>
				</ul>
				<hr class="greyLine">
			</div>
			<div class="tabContentBox">

				<div class="tabContent tabContent-1 selected">
					
					<h2>Info</h2>
					<div class="greyBox">
						<div class="innerBox">
							Resource id: <b><?= $resource->id ?></b><br>
							<br>
							Resource name: <b><?= $resource->name ?></b><br>
							<br>
							Source class: <b><?= $resource->sourceClass ?></b><br>
							<br>
							Source method: <b><?= $resource->sourceMethod ?></b><br>
							<br>
							<? if ($resource->sourceStatic == 0): ?>
								Static call: <b>FALSE (called on object)</b><br>
								<br>
							<? else: ?>
								Static call: <b>TRUE (called on class)</b><br>
								<br>
							<? endif; ?>

							<? if ($resource->status != 0): ?>
								Status: <b>VALID</b><br>
								<br>
							<? else: ?>
								Status: <b>INVALID</b><br>
								<br>
							<? endif; ?>

							Creation time: <b><?= date('Y-m-d H:i:s', $resource->creationTime) ?></b><br>
								<br>

							<? if ($resource->expirationTime != 0): ?>
								Expiration time: <b><?= date('Y-m-d d.m.Y H:i:s', $resource->expirationTime) ?></b><br>
								<br>
							<? else: ?>
								Expiration time: <b>Never</b><br>
								<br>
							<? endif; ?>

							<? if ($resource->expirationTimeFast != 0): ?>
								Expiration time: <b><?= date('Y-m-d H:i:s', $resource->expirationTimeFast) ?></b><br>
								<br>
							<? else: ?>
								Expiration time: <b>Never</b><br>
								<br>
							<? endif; ?>

							Queryable cache hits: <b><?= $resource->hits ?></b><br>
							<br>

							Invalidation policy: <b><?= $resource->invalidationPolicy ?></b><br>
						</div>
					</div>
					
				</div>
				
				<div class="tabContent tabContent-2">
					
					<h2>Value</h2>
					<div class="greyBox">
					<?= var_dump($resource->value) ?>
					</div>
					
				</div>
				
				<div class="tabContent tabContent-3">

					<h2>Properties</h2>
					<div class="greyBox">
						<div class="innerBox">
						
					<?
					
						$propDefs = $resource->getPropertyDefinitions();
						if(count($propDefs) == 0) {
							return;
						}
						?>
						Properties:<br>
						<?	foreach ($propDefs as $propName => $propValue): ?>
								<? if(isset($resource->properties[$propName])): ?>
								<pre><?= $view->escape($propName) ?> = <?= $view->escape(is_string($resource->properties[$propName])?$resource->properties[$propName]:json_encode($resource->properties[$propName])) ?></pre>
								<? endif; ?>
						<? endforeach; ?>

						</div>
					</div>
					
				</div>
				
			</div>
		</div>
		
		<div class="floatMenu">
			<ul>
				<li><?= $view->link('PREVIEW INVALIDATION', 'actionPreviewRebuildId', array($resource->name, $resource->id), array('title' => 'Preview invalidation of cached esource', 'class' => 'linkButtonYellow')) ?></li>
				<li><?= $view->link('REBUILD', 'actionRebuildId', array($resource->name, $resource->id, \Foomo\Cache\Invalidator::POLICY_INSTANT_REBUILD), array('title' => 'Re-create the cached object. Creation time is set to NOW. Expiration times for fast and queryable caches are updated. Dependent objects are also recreated.', 'class' => 'linkButtonYellow')) ?></li>
				<li><?= $view->link('INVALIDATE', 'actionRebuildId', array($resource->name, $resource->id, \Foomo\Cache\Invalidator::POLICY_INVALIDATE), array('title' => 'Set cached object STATUS property to INVALID, resulting in re-creation of the object when next requested from the cache. Dependent objects are also invalidate.', 'class' => 'linkButtonYellow')) ?></li>
				<li><?= $view->link('DELETE', 'actionRebuildId', array($resource->name, $resource->id, \Foomo\Cache\Invalidator::POLICY_DELETE), array('title' => 'Delete cached object from fast and queryable caches. Dependent objects are also deleted.', 'class' => 'linkButtonYellow')) ?></li>
			</ul>
		</div>
		<br>
	</div>
	
</div>

<? endforeach; ?>
