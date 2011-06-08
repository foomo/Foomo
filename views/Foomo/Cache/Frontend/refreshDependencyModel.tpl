<?php
/* @var $view Foomo\MVC\View */
/* @var $model Foomo\Cache\Frontend\Model */
?>

<?= $view->partial('header') ?>
<?= $view->partial('menu') ?>


<h2>Cached resources named : <?= $view->escape($model->currentResourceName) ?></h2>

<?= $view->partial('resourceAnnotation') ?>
<?= $view->partial('resourcePropertiesDefinitions',array('resourceName' =>$model->currentResourceName)); ?>
<?= $view->partial('storageStatus',array('resourceName' => $model->currentResourceName)); ?>
<div>
	<?= $view->link('[PREVIEW INVALIDATION]', 'actionPreviewRebuildResourcesWithName', array($model->currentResourceName), 'Preview invalidation') ?>
	| <?= $view->link('[REBUILD]', 'rebuildResourcesWithName', array($model->currentResourceName, \Foomo\Cache\Invalidator::POLICY_INSTANT_REBUILD), 'Re-create all cached objects with the selected resource name. The dependency tree is traversed in full depth and dependent objects from other resources are re-created. Creation time is set to NOW. Expiration times for fast and queryable caches are updated.') ?>
	| <?= $view->link('[INVALIDATE]', 'rebuildResourcesWithName', array($model->currentResourceName, \Foomo\Cache\Invalidator::POLICY_INVALIDATE), "Set the STATUS property of all cached object belonging to the selected resource to INVALID. The Dependency tree is traversed in depth and depending objects belonging to dependent resources are also invalidated. This results in re-creation of the object whe it is next requested.") ?>
	| <?= $view->link('[DELETE]', 'rebuildResourcesWithName', array($model->currentResourceName, \Foomo\Cache\Invalidator::POLICY_DELETE), 'Delete all cached objects with the selected resource name. The dependency tree is traversed in full depth and dependent objects from other resources are also deleted.') ?>
	| <?= $view->link('[ADVANCED]', 'advancedInvalidation', array($model->currentResourceName), 'Perform invalidation on a set of objects defined by a complex query expression using a user selectable invalidation policy.') ?>
	| <?= $view->link('[SETUP '. $model->currentResourceName. ']', 'setUpOne', array($model->currentResourceName), 'Re-create storage structure for resource called ' . $model->currentResourceName) ?>
	| <?= $view->link('[VALIDATE STORAGE STRUCTURE]', 'actionValidateStorageStructure', array($model->currentResourceName), 'Validates existing storage structures against annotation') ?>
	| <?= $view->link('[REFRESH DEPENDENCY MODEL]', 'actionRefreshDependencyModel', array($model->currentResourceName), 'Refreshes the dependency model for the resource') ?>
</div>

<?= $view->partial('resourcesList', array('resources'=>$model->getCachedResourcesList())) ?>


<h2>---</h2>
