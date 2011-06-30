<nav id="menuSub">
	<ul>
		<li><?= $view->partial('menuButton', array('url' => 'default', 'name' => 'Overview' ), 'Foomo\\Frontend') ?></li>
		<li><?= $view->partial('menuButton', array('url' => 'createNew', 'name' => 'Create new module' ), 'Foomo\\Frontend') ?></li>
	</ul>
</nav>
