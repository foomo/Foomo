<ul>
	<li><?= $view->link('List resources', 'default') ?></li>
	<li><?= $view->link('Reset cache', 'checkDialog', array('reset'), array('title' => 'Erase all cached objects and re-create cache storage structures.')) ?></li>
	<li><?= $view->link('Populate fast cache', 'checkDialog', array('populateFastCache'), array('title' => 'Fills fast cache with persisted resources from the queryable cache')) ?></li>
	<li><?= $view->link('Refresh dependency model', 'refreshDependencyModelAll', array(), array('title' => 'Refreshes the cached dependency model')) ?></li>
</ul>
