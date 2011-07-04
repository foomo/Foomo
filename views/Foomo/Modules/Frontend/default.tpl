<?

use Foomo\Modules\Manager;

?>
<?= $view->partial('menu') ?>
<?
$enabledModules = Manager::getEnabledModules();
// @todo implement module status
$resI = 0;
?>
<div id="appContent">
	
	<?
	$orderedModules = Manager::getAvailableModules();
	sort($orderedModules);
	foreach($orderedModules as $availableModule):

		$moduleEnabled = 'disabled';
		// the local vars here need to be renamed ...
		$modStat = Manager::getModuleStatus($availableModule);
		$modResources = Manager::getModuleResources($availableModule);
		$resourceCount = count($modResources);
		if(in_array($availableModule, $enabledModules)) {
			$moduleEnabled = 'enabled';
		}
		$depsOk = true;
		foreach(Manager::getRequiredModuleResources($availableModule) as $reqiredModuleResource) {
			if(!$reqiredModuleResource->resourceValid()) {
				$depsOk = false;
				break;
			}
		}
		$hintClass = $modStat==Manager::MODULE_STATUS_OK?'valid':'invalid';
		$hasFrontEnd = Foomo\Modules\Manager::moduleHasFrontend($availableModule);
		$hasMVCFrontEnd = Foomo\Modules\Manager::moduleHasMVCFrontend($availableModule);
	?>
	<form action="<?= $view->url('actionUpdateModules'); ?>" method="POST" id="moduleForm">
	<div class="toggleBox">
		<div class="toogleButton">
			<div class="toggleOpenIcon">+</div>
			<div class="toggleOpenContent">
				<div class="floatLeftBox" style="width:400px;">
					<?= $availableModule ?> v <?= constant(str_replace('.', '\\', $availableModule) . '\\Module::VERSION') ?>
				</div>
				<div class="floatLeftBox" style="width:200px;">
					<? if($hasMVCFrontEnd && !$hasFrontEnd): ?>
						<?= $view->link('Open frontend', 'showMVCApp', array($availableModule), array('class' => 'linkButtonYellow')) ?>
					<? elseif($availableModule == \Foomo\Module::NAME): ?>
						&nbsp;
					<? elseif(!$hasFrontEnd && !$hasFrontEnd): ?>
						&nbsp;
					<? else: ?>
						<a class="linkButtonYellow" href="<?= $view->escape(Foomo\ROOT_HTTP . '/modules/' . $availableModule) ?>" target="_blank">Open a new frontend</a>
					<? endif; ?>
				</div>
				<div class="floatLeftBox" style="width:140px;"><span style="font-weight: normal;">Status:</span> <?= $modStat==Manager::MODULE_STATUS_OK?'<span class="textGreen">ok</span>':'<span class="textRed">check</span>'; ?></div>
				<div class="floatLeftBox"><span style="font-weight: normal;">Action:</span>
				
					<? if($availableModule == \Foomo\Module::NAME): ?>
						<span title="well you do not want to fiddle around with the core ;)">none</span>
					<? elseif($moduleEnabled != 'enabled' && !$depsOk): ?>
						<span title="enable dependencies first">none</span>
					<? elseif($moduleEnabled != 'enabled'): ?>
						<a title="enable module <?= $availableModule ?>" href="#" onclick="document.getElementById('modField<?php echo $availableModule ?>').value='enable';document.getElementById('moduleForm').submit();"><span class="textGreen">enable</span></a><input type="hidden" value="disable" name="moduleStates[<?php echo $availableModule ?>]" id="modField<?php echo $availableModule ?>">
					<? else: ?>
						<a title="disable module <?= $availableModule ?>" href="#" onclick="document.getElementById('modField<?php echo $availableModule ?>').value='disable';document.getElementById('moduleForm').submit();"><span class="textRed">disable</span></a><input type="hidden" value="enable" name="moduleStates[<?php echo $availableModule ?>]" id="modField<?php echo $availableModule ?>">
					<? endif; ?>
				
				</div>
			</div>
		</div>
		<div class="toggleContent">
			
			<div class="innerBox">
				<div class="halfBox">
					Description:<br>
					<b><?= Manager::getModuleDescription($availableModule); ?></b>
				</div>
				
				<div class="halfBox">
					<? if( Manager::getRequiredModules($availableModule) ): ?>
					Dependencies:<br>
					<b><?= implode(', ', Manager::getRequiredModules($availableModule)); ?></b>
					<? endif; ?>
				</div>
			</div>
			<br>
			<div class="greyBox">
				<div class="innerBox">
				<?php
					switch($modStat) {
						case Manager::MODULE_STATUS_OK:
							$msg = '<p>everything is cool with this module</p>';
							break;
						case Manager::MODULE_STATUS_REQUIRED_MODULES_MISSING:
							$msg = '<p>there are other modules, that need to be enabled - check the dependencies</p>';
							break;
						case Manager::MODULE_STATUS_RESOURCES_INVALID:
							$msg = '<p>invalid resources</p>';
							break;
						default:
							$msg = $modStat;
					}
					echo $msg;
					echo $view->partial('moduleResources', array('moduleName' => $availableModule));
				?>	
				</div>
			</div>
			
		</div>
	</div>
	</form>
	<?php endforeach; ?>
	
	<hr class="greyLine">
	
	<?= $view->link('Try to create missing resources for all enabled modules', 'actionTryCreateAllModuleResources', array(), array('class' => 'linkButtonYellow')); ?>
	
</div>
