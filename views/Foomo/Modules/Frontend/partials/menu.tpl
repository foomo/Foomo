<nav id="menuSub">
	<ul>
		<li><?= $view->link('overview', 'default') ?></li>
		<li><?= $view->link('create new module', 'createNew') ?></li>
		<li><?= $view->link('Try to create missing resources for all enabled modules', 'actionTryCreateAllModuleResources') ?></li>
		<li><a id="showModulesButton" href="#" onclick="showAllResources();">show module resources</a></li>
		<li><a id="hideModulesButton" style="display:none" href="#" onclick="hideAllResources();">hide module resources</a></li>
	</ul>
</nav>
