
<?
	foreach ($resources as $resource):
		 $valid = ($resource->status == \Foomo\Cache\CacheResource::STATUS_VALID) || ($resource->expirationTime < \time());
		 $class = $valid?'valid':'invalid';
		 $msg = $valid?'valid':'invalid';
		 
		 $propDefs = $resource->getPropertyDefinitions();
		 $propDefsOut = '';
		 $rows = 1;
		 
		 foreach ($propDefs as $propName => $propValue):
			 if(isset($resource->properties[$propName])):
				 $propDefsOut .= '<div class="horizontalBox">'.$view->escape($propName).': <span style="font-weight: normal;">'.$view->escape(is_string($resource->properties[$propName])?$resource->properties[$propName]:json_encode($resource->properties[$propName])).'<span></div>'.  chr(10);
				 $rows++;
			 endif;
		 endforeach;
?>

<div class="toggleBox">
	<div class="toogleButton" style="height: <? if($rows > 1): ?><?= (17*$rows)+28  ?><? else: ?>28<? endif; ?>px">
		<div class="toggleOpenIcon">+</div>
		<div class="toggleOpenContent"><?= $view->escape($resource->name) ?>(<? if($rows > 1): ?><br><? endif; ?><?= $propDefsOut ?> )</div>
	</div>
	<div class="toggleContent">
		
		<div class="floatMenu">
			<ul>
				<li><?= $view->link('PREVIEW INVALIDATION', 'actionPreviewRebuildId', array($resource->name, $resource->id), array('title' => 'Preview invalidation of cached esource', 'class' => 'linkButtonYellow overlay')) ?></li>
				<li><?= $view->link('REBUILD', 'actionRebuildId', array($resource->name, $resource->id, \Foomo\Cache\Invalidator::POLICY_INSTANT_REBUILD), array('title' => 'Re-create the cached object. Creation time is set to NOW. Expiration times for fast and queryable caches are updated. Dependent objects are also recreated.', 'class' => 'linkButtonYellow overlay')) ?></li>
				<li><?= $view->link('INVALIDATE', 'actionRebuildId', array($resource->name, $resource->id, \Foomo\Cache\Invalidator::POLICY_INVALIDATE), array('title' => 'Set cached object STATUS property to INVALID, resulting in re-creation of the object when next requested from the cache. Dependent objects are also invalidate.', 'class' => 'linkButtonYellow overlay')) ?></li>
				<li><?= $view->link('DELETE', 'actionRebuildId', array($resource->name, $resource->id, \Foomo\Cache\Invalidator::POLICY_DELETE), array('title' => 'Delete cached object from fast and queryable caches. Dependent objects are also deleted.', 'class' => 'linkButtonRed overlay')) ?></li>
			</ul>
		</div>
		
		<hr class="greyFullLine">
		
		<div class="tabBox">
			<div class="tabNavi">
				<ul>
					<li class="selected">Info</li>
					<li>Value</li>
				</ul>
				<hr class="greyLine">
			</div>
			<div class="tabContentBox">

				<div class="tabContent tabContent-1 selected">
					
					<h2>Info</h2>
					<div class="greyBox">
						<div class="innerBox">
							<ul class="listBox">
								<li><b>Resource id:</b> <?= $resource->id ?></li>
								<li><b>Resource name:</b> <?= $resource->name ?></li>
								<li><b>Source class:</b> <?= $resource->sourceClass ?></li>
								<li><b>Source method:</b> <?= $resource->sourceMethod ?></li>
							<? if ($resource->sourceStatic == 0): ?>
								<li><b>Static call:</b> FALSE (called on object)</li>
							<? else: ?>
								<li><b>Static call:</b> TRUE (called on class)</li>
							<? endif; ?>

							<? if ($resource->status != 0): ?>
								<li><b>Status:</b> VALID</li>
							<? else: ?>
								<li><b>Status:</b> INVALID</li>
							<? endif; ?>

								<li><b>Creation time:</b> <?= date('Y-m-d H:i:s', $resource->creationTime) ?></li>

							<? if ($resource->expirationTime != 0): ?>
								<li><b>Expiration time:</b> <?= date('Y-m-d H:i:s', $resource->expirationTime) ?></li>
							<? else: ?>
								<li><b>Expiration time:</b> Never</b></li>
							<? endif; ?>

							<? if ($resource->expirationTimeFast != 0): ?>
								<li><b>Expiration time:</b> <?= date('Y-m-d H:i:s', $resource->expirationTimeFast) ?></li>
							<? else: ?>
								<li><b>Expiration time:</b> Never</li>
							<? endif; ?>

								<li><b>Queryable cache hits: </b><?= $resource->hits ?></li>
								<li><b>Invalidation policy: </b><?= $resource->invalidationPolicy ?></li>
							</ul>
						</div>
					</div>
					
				</div>
				
				<div class="tabContent tabContent-2">
					
					<h2>Value</h2>
					<div class="greyBox">
						<pre><?
								ini_set('html_errors', 'Off');
								ob_start();
								var_dump($resource->value);
								echo $view->escape(ob_get_clean());
							?></pre>
					</div>
					
				</div>
				
			</div>
		</div>
		
	</div>
</div>

<? endforeach; ?>
