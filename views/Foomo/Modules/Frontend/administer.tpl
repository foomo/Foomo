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
	<p><a id="showModulesButton" href="#" onclick="showAllResources();">show module resources</a></p>
	<p><a id="hideModulesButton" style="display:none" href="#" onclick="hideAllResources();">hide module resources</a></p>
	<p><?= $view->link('Try to create missing resources for all enabled modules', 'actionTryCreateAllModuleResources') ?></p>
	<form action="<?= $view->url('actionUpdateModules'); ?>" method="POST" id="moduleForm">
	<table title="foomo modules" id="moduleTable">
		<tr>
			<td>&nbsp;</td>
			<td>Name</td>
			<td>Description</td>
			<td>Dependencies</td>
			<td>Status</td>
			<td>Action</td>
		</tr>
	<?php foreach(Manager::getAvailableModules() as $availableModule):?>
	<?php
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
	?>
		<tr>
			<td style="text-align:center;"><?php echo '<img src="' . \Foomo\ROOT_HTTP . '/img/' . $moduleEnabled . '.gif" width="12" height="12" title="'. $moduleEnabled .'" alt="'. $moduleEnabled .'">'; ?></td>
			<td><?= $availableModule; ?> / <?= constant(str_replace('.', '\\', $availableModule) . '\\Module::VERSION') ?></td>
			<td><?= Manager::getModuleDescription($availableModule); ?></td>
			<td><?= implode(', ', Manager::getRequiredModules($availableModule)); ?></td>
			<td class="<?= $hintClass ?>"><?= $modStat==Manager::MODULE_STATUS_OK?'ok':'check'; ?></td>
			<td>
				<? if($availableModule == \Foomo\Module::NAME): ?>
					<span title="well you do not want to fiddle around with the core ;)">none</span>
				<? elseif($moduleEnabled != 'enabled' && !$depsOk): ?>
					<span title="enable dependencies first">none</span>
				<? elseif($moduleEnabled != 'enabled'): ?>
					<a title="enable module <?= $availableModule ?>" href="#" onclick="document.getElementById('modField<?php echo $availableModule ?>').value='enable';document.getElementById('moduleForm').submit();">enable</a><input type="hidden" value="disable" name="moduleStates[<?php echo $availableModule ?>]" id="modField<?php echo $availableModule ?>">
				<? else: ?>
					<a title="disable module <?= $availableModule ?>" href="#" onclick="document.getElementById('modField<?php echo $availableModule ?>').value='disable';document.getElementById('moduleForm').submit();">disable</a><input type="hidden" value="enable" name="moduleStates[<?php echo $availableModule ?>]" id="modField<?php echo $availableModule ?>">
				<? endif; ?>
			</td>
		</tr>
		<?php if($resourceCount > 0 || $modStat != Manager::MODULE_STATUS_OK): ?>
		<tr>
			<td style="display:none" id="resourceDisplay_<?php echo $resI ;$resI++; ?>">&nbsp;</td>
			<td style="display:none;" id="resourceDisplay_<?php echo $resI ;$resI++; ?>" colspan="5" class="<?php echo $hintClass ?>">
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
			</td>
		</tr>
		<?php endif; ?>
	<?php endforeach; ?>
	</table>
	<noscript>
		<p>
			<i>it is more fun with js turned on - change one at a time</i>
		</p>
		<input type="submit" value="update">
	</noscript>
	</form>
</div>
