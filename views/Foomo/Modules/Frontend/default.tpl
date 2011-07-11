<?

use Foomo\Modules\Manager;

// this is important to make sure module class constants are available
Manager::loadAvailableModuleClasses();

?>
<?= $view->partial('menu') ?>

<div id="appContent">
	
	<div class="rightBox">
		<?= $view->link('Try to create missing resources for all enabled modules', 'actionTryCreateAllModuleResources', array(), array('class' => 'linkButtonYellow')); ?>
	</div>
	<h2>Enabled Modules </h2>
	<?
	$enabledModules = Manager::getEnabledModules();
	sort($enabledModules);
	?>
	<? foreach($enabledModules as $module): ?>
		<?= $view->partial('moduleOverviewItem', array('module' => $module))?>
	<? endforeach; ?>
	<br>
	<br>
	<h2>Avialable Modules</h2>
	<?
	$avilableModules = Manager::getAvailableModules();
	$otherModules = Array();
	foreach($avilableModules as $avilableModule){
			if(!in_array($avilableModule, $enabledModules)) {
				array_push($otherModules, $avilableModule);
			}
	}
	sort($otherModules);
	?>
	<? foreach($otherModules as $module): ?>
		<?= $view->partial('moduleOverviewItem', array('module' => $module))?>
	<? endforeach; ?>
</div>
