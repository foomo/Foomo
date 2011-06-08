<?= $view->partial('menu') ?>
<div id="appContent">
	<ul>
	<? foreach(Foomo\Modules\Manager::getEnabledModules() as $enabledModuleName):
		if($enabledModuleName == \Foomo\Module::NAME) { continue; }
	?>
		<?
		  $hasFrontEnd = Foomo\Modules\Manager::moduleHasFrontend($enabledModuleName);
		  $hasMVCFrontEnd = Foomo\Modules\Manager::moduleHasMVCFrontend($enabledModuleName);
		?>
		<? if($hasFrontEnd || $hasMVCFrontEnd): ?>
		<li>
			<? if($hasMVCFrontEnd && !$hasFrontEnd): ?>
				<?= $view->link($enabledModuleName, 'showMVCApp', array($enabledModuleName)) ?>
			<? else: ?>
				<? // $view->link($enabledModuleName, 'showFrameApp', array($enabledModuleName)) ?>
				<a href="<?= $view->escape(Foomo\ROOT_HTTP . '/modules/' . $enabledModuleName) ?>"><?= $enabledModuleName ?></a>

			<? endif; ?>
			<div style="color:grey;padding-left:10px;font-size:10px;"><?= Foomo\Modules\Manager::getModuleDescription($enabledModuleName) ?></div>
		</li>
		<? endif; ?>
	<? endforeach; ?>
	</ul>
</div>
