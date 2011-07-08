
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
							<ul class="listBox">
								<li>Resource id: <b><?= $resource->id ?></b></li>
								<li>Resource name: <b><?= $resource->name ?></b></li>
								<li>Source class: <b><?= $resource->sourceClass ?></b></li>
								<li>Source method: <b><?= $resource->sourceMethod ?></b></li>
							<? if ($resource->sourceStatic == 0): ?>
								<li>Static call: <b>FALSE (called on object)</b></li>
							<? else: ?>
								<li>Static call: <b>TRUE (called on class)</b></li>
							<? endif; ?>

							<? if ($resource->status != 0): ?>
								<li>Status: <b>VALID</b></li>
							<? else: ?>
								<li>Status: <b>INVALID</b></li>
							<? endif; ?>

								<li>Creation time: <b><?= date('Y-m-d H:i:s', $resource->creationTime) ?></b></li>

							<? if ($resource->expirationTime != 0): ?>
								<li>Expiration time: <b><?= date('Y-m-d d.m.Y H:i:s', $resource->expirationTime) ?></b></li>
							<? else: ?>
								<li>Expiration time: <b>Never</b></li>
							<? endif; ?>

							<? if ($resource->expirationTimeFast != 0): ?>
								<li>Expiration time: <b><?= date('Y-m-d H:i:s', $resource->expirationTimeFast) ?></b></li>
							<? else: ?>
								<li>Expiration time: <b>Never</b></li>
							<? endif; ?>

								<li>Queryable cache hits: <b><?= $resource->hits ?></b></li>
								<li>Invalidation policy: <b><?= $resource->invalidationPolicy ?></b></li>
							</ul>
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
				<li><?= $view->link('PREVIEW INVALIDATION', 'actionPreviewRebuildId', array($resource->name, $resource->id), array('title' => 'Preview invalidation of cached esource', 'class' => 'linkButtonYellow overlay')) ?></li>
				<li><?= $view->link('REBUILD', 'actionRebuildId', array($resource->name, $resource->id, \Foomo\Cache\Invalidator::POLICY_INSTANT_REBUILD), array('title' => 'Re-create the cached object. Creation time is set to NOW. Expiration times for fast and queryable caches are updated. Dependent objects are also recreated.', 'class' => 'linkButtonYellow overlay')) ?></li>
				<li><?= $view->link('INVALIDATE', 'actionRebuildId', array($resource->name, $resource->id, \Foomo\Cache\Invalidator::POLICY_INVALIDATE), array('title' => 'Set cached object STATUS property to INVALID, resulting in re-creation of the object when next requested from the cache. Dependent objects are also invalidate.', 'class' => 'linkButtonYellow overlay')) ?></li>
				<li><?= $view->link('DELETE', 'actionRebuildId', array($resource->name, $resource->id, \Foomo\Cache\Invalidator::POLICY_DELETE), array('title' => 'Delete cached object from fast and queryable caches. Dependent objects are also deleted.', 'class' => 'linkButtonRed overlay')) ?></li>
			</ul>
			<div style="clear: both;"></div>
		</div>
		
	</div>
</div>

<? endforeach; ?>
