<nav id="menuSub">
	<ul>
		<? if(empty($allowedDomains)): ?>
			<li><?= $view->partial('menuButton', array('url' => 'default', 'name' => 'Overview' ), 'Foomo\Frontend') ?></li>
			<li><?= $view->partial('menuButton', array('url' => 'createDomain', 'name' => 'Create new auth domain' ), 'Foomo\Frontend') ?></li>
		<? endif; ?>
	</ul>
</nav>
