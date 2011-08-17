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

	/*
				<? if($module == \Foomo\Module::NAME): ?>
					<span title="well you do not want to fiddle around with the core ;)">none</span>
				<? elseif($moduleEnabled != 'enabled' && !$depsOk): ?>
					<span title="enable dependencies first">none</span>
				<? else ?>
	 */


?>
<div class="toggleBox">
	<div class="toogleButton">
		<div class="toggleOpenIcon">+</div>
		<div class="toggleOpenContent">
			<?= $module ?> v <?= constant(str_replace('.', '\\', $module) . '\\Module::VERSION') ?>
		</div>
		<div class="toggleOpenInfo">

			<div class="floatRightSpaceBox" style="width:70px;">
				<? if($module == \Foomo\Module::NAME): ?>
					&nbsp;
				<? elseif($moduleEnabled != 'enabled' && !$depsOk): ?>
					&nbsp;
				<? elseif($moduleEnabled != 'enabled'): ?>
					<?= $view->link('Enable', 'enableModule', array($module), array('class' => 'linkButtonSmallYellow')) ?>
				<? else: ?>
					<?= $view->link('Disable', 'disableModule', array($module), array('class' => 'linkButtonSmallYellow')) ?>
				<? endif; ?>
			</div>

			<div class="floatRightSpaceBox">
				<span style="font-weight: normal;">Status:</span> <?= $modStat==Manager::MODULE_STATUS_OK?'<span class="textGreen">ok</span>':'<span class="textRed">check</span>'; ?>
			</div>

			<div class="floatRightSpaceBox">
				<? if($hasMVCFrontEnd && !$hasFrontEnd): ?>
					<?= $view->link('Open module frontend', 'showMVCApp', array($module), array('class' => 'linkButtonSmallYellow')) ?>
				<? elseif($module == \Foomo\Module::NAME): ?>
					&nbsp;
				<? elseif(!$hasFrontEnd && !$hasFrontEnd): ?>
					&nbsp;
				<? else: ?>
					<a class="linkButtonSmallYellow" href="<?= $view->escape(Foomo\ROOT_HTTP . '/modules/' . $module) ?>" target="_blank">Open a new module frontend</a>
				<? endif; ?>
			</div>

		</div>
	</div>
	<div class="toggleContent">

		<div class="innerBox">
			<div class="halfBox">
				<b>Description:</b><br>
				<?= Manager::getModuleDescription($module); ?>
			</div>

			<div class="halfBox">
				<? if( Manager::getRequiredModules($module) ): ?>
				<b>Dependencies:</b><br>
				<?= implode(', ', Manager::getRequiredModules($module)); ?>
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