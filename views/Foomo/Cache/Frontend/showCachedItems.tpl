<?php
/* @var $view Foomo\MVC\View */
/* @var $model Foomo\Cache\Frontend\Model */
?>

<?= $view->partial('header') ?>
<?= $view->partial('menu') ?>

<div class="rightBox">
	<?= $view->link('Back', 'default', array(), array('class' => 'linkButtonYellow')) ?>
</div>

<h2><?= $view->escape($model->currentResourceName) ?></h2>



<div class="whiteBox">
	<div style="margin:10px;">
		<?= $view->partial('resourceAnnotation') ?>
		<hr class="greyLine">
		<?= $view->partial('resourcePropertiesDefinitions',array('resourceName' =>$model->currentResourceName)); ?>
		<hr class="greyLine">
		<?= $view->partial('storageStatus',array('resourceName' => $model->currentResourceName)); ?>
	</div>
</div>
<br>
<div class="floatMenu">
	<ul>
		<li><?= $view->link('PREVIEW INVALIDATION', 'actionPreviewRebuildResourcesWithName', array($model->currentResourceName), array('title' => 'Preview invalidation', 'class' => 'linkButtonYellow' )) ?></li>
		<li><?= $view->link('REBUILD', 'rebuildResourcesWithName', array($model->currentResourceName, \Foomo\Cache\Invalidator::POLICY_INSTANT_REBUILD), array('title' => 'Re-create all cached objects with the selected resource name. The dependency tree is traversed in full depth and dependent objects from other resources are re-created. Creation time is set to NOW. Expiration times for fast and queryable caches are updated.', 'class' => 'linkButtonYellow' )) ?></li>
		<li><?= $view->link('INVALIDATE', 'rebuildResourcesWithName', array($model->currentResourceName, \Foomo\Cache\Invalidator::POLICY_INVALIDATE), array('title' => "Set the STATUS property of all cached object belonging to the selected resource to INVALID. The Dependency tree is traversed in depth and depending objects belonging to dependent resources are also invalidated. This results in re-creation of the object whe it is next requested.", 'class' => 'linkButtonYellow' )) ?></li>
		<li><?= $view->link('ADVANCED', 'advancedInvalidation', array($model->currentResourceName), array('title' => 'Perform invalidation on a set of objects defined by a complex query expression using a user selectable invalidation policy.', 'class' => 'linkButtonYellow' )) ?></li>
		<li><?= $view->link('SETUP ', 'setUpOne', array($model->currentResourceName), array('title' => 'Re-create storage structure for resource called ' . $model->currentResourceName, 'class' => 'linkButtonYellow' )) ?></li>
		<li><?= $view->link('VALIDATE STORAGE STRUCTURE', 'actionValidateStorageStructure', array($model->currentResourceName), array('title' => 'Validates existing storage structures against annotation', 'class' => 'linkButtonYellow' )) ?></li>
		<li><?= $view->link('REFRESH DEPENDENCY MODEL', 'actionRefreshDependencyModel', array($model->currentResourceName), array('title' => 'Refreshes the dependency model for the resource', 'class' => 'linkButtonYellow' )) ?></li>
		<li><?= $view->link('DELETE', 'rebuildResourcesWithName', array($model->currentResourceName, \Foomo\Cache\Invalidator::POLICY_DELETE), array('title' => 'Delete all cached objects with the selected resource name. The dependency tree is traversed in full depth and dependent objects from other resources are also deleted.', 'class' => 'linkButtonRed' )) ?></li>
	</ul>
</div>

<hr>

<?= $view->partial('resourcesList', array('resources'=> $model->getCachedResourcesList())) ?>

