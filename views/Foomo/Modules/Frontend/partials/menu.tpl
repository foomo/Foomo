<nav id="menuSub">
	<ul>
		<li><?= $view->partial('buttonYellow', array('url' => 'default', 'name' => 'Overview' ), 'Foomo\Frontend') ?></li>
		<li><?= $view->partial('buttonYellow', array('url' => 'createNew', 'name' => 'Create new module' ), 'Foomo\Frontend') ?></li>
		<li><?= $view->partial('buttonYellow', array('url' => 'actionTryCreateAllModuleResources', 'name' => 'Try to create missing resources for all enabled modules' ), 'Foomo\Frontend') ?></li>
	</ul>
</nav>
