<?

	use Foomo\Modules\Manager;

	$enabledModules = Manager::getEnabledModules();
	
	
	$moduleEnabled = 'disabled';
	// the local vars here need to be renamed ...
	$modStat = Manager::getModuleStatus($module);
	$modResources = Manager::getModuleResources($module);
	$resourceCount = count($modResources);
	if(in_array($module, $enabledModules)) {
		$moduleEnabled = 'enabled';
	}
	$depsOk = true;
	foreach(Manager::getRequiredModuleResources($module) as $reqiredModuleResource) {
		if(!$reqiredModuleResource->resourceValid()) {
			$depsOk = false;
			break;
		}
	}
	$hintClass = $modStat==Manager::MODULE_STATUS_OK?'valid':'invalid';
	$hasFrontEnd = Foomo\Modules\Manager::moduleHasFrontend($module);
	$hasMVCFrontEnd = Foomo\Modules\Manager::moduleHasMVCFrontend($module);
?>
<div class="toggleBox">
	<div class="toogleButton">
		<div class="toggleOpenIcon">+</div>
		<div class="toggleOpenContent">
			<div class="floatLeftBox" style="width:400px;">
				<?= $module ?> v <?= constant(str_replace('.', '\\', $module) . '\\Module::VERSION') ?>
			</div>
			<div class="floatLeftBox" style="width:200px;">
				<? if($hasMVCFrontEnd && !$hasFrontEnd): ?>
					<?= $view->link('Open frontend', 'showMVCApp', array($module), array('class' => 'linkButtonYellow')) ?>
				<? elseif($module == \Foomo\Module::NAME): ?>
					&nbsp;
				<? elseif(!$hasFrontEnd && !$hasFrontEnd): ?>
					&nbsp;
				<? else: ?>
					<a class="linkButtonYellow" href="<?= $view->escape(Foomo\ROOT_HTTP . '/modules/' . $module) ?>" target="_blank">Open a new frontend</a>
				<? endif; ?>
			</div>
			<div class="floatLeftBox" style="width:140px;"><span style="font-weight: normal;">Status:</span> <?= $modStat==Manager::MODULE_STATUS_OK?'<span class="textGreen">ok</span>':'<span class="textRed">check</span>'; ?></div>
			<div class="floatLeftBox"><span style="font-weight: normal;">Action:</span>

				<? if($module == \Foomo\Module::NAME): ?>
					<span title="well you do not want to fiddle around with the core ;)">none</span>
				<? elseif($moduleEnabled != 'enabled' && !$depsOk): ?>
					<span title="enable dependencies first">none</span>
				<? elseif($moduleEnabled != 'enabled'): ?>
					<?= $view->link('enable', 'enableModule', array($module)) ?>
				<? else: ?>
					<!--
					<a title="disable module <?= $module ?>" href="#" onclick="document.getElementById('modField<?php echo $module ?>').value='disable';document.getElementById('moduleForm').submit();"><span class="textRed">disable</span></a><input type="hidden" value="enable" name="moduleStates[<?php echo $module ?>]" id="modField<?php echo $module ?>">
					-->
					<?= $view->link('disable', 'disableModule', array($module)) ?>
				<? endif; ?>

			</div>
		</div>
	</div>
	<div class="toggleContent">

		<div class="innerBox">
			<div class="halfBox">
				Description:<br>
				<b><?= Manager::getModuleDescription($module); ?></b>
			</div>

			<div class="halfBox">
				<? if( Manager::getRequiredModules($module) ): ?>
				Dependencies:<br>
				<b><?= implode(', ', Manager::getRequiredModules($module)); ?></b>
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
				echo $view->partial('moduleResources', array('moduleName' => $module));
			?>	
			</div>
		</div>

	</div>
</div>