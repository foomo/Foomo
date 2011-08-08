<?

use Foomo\Modules\Manager;

// this is important to make sure module class constants are available
Manager::loadAvailableModuleClasses();

$availableModules = Manager::getAvailableModules();
sort($availableModules);

$enabledModules = Manager::getEnabledModules();
sort($enabledModules);


?>
<?= $view->partial('menu') ?>

<div id="appContent">
	
	<div class="rightBox">
		<?= $view->link('Try to create missing resources for all enabled modules', 'actionTryCreateAllModuleResources', array(), array('class' => 'linkButtonYellow overlay')); ?>
	</div>
	<h2>Enabled Modules </h2>
	<? foreach($enabledModules as $module): ?>
		<?= $view->partial('moduleOverviewItem', array('module' => $module))?>
	<? endforeach; ?>
	<br>
	<br>
	<h2>Available Modules</h2>
	<? if($availableModules != $enabledModules): ?>
		<?
		$otherModules = Array();
		foreach($availableModules as $avilableModule){
				if(!in_array($avilableModule, $enabledModules)) {
					array_push($otherModules, $avilableModule);
				}
		}
		sort($otherModules);
		?>
		<? foreach($otherModules as $module): ?>
			<?= $view->partial('moduleOverviewItem', array('module' => $module))?>
		<? endforeach; ?>
	<? else: ?>
		<p>All available modules are enabled</p>
	<? endif; ?>
</div>
