<? /* @var $view Foomo\MVC\View */ ?>
<nav id="menuSub">
	<ul>
		<li><?= $view->partial('menuButton', array('url' => 'default', 'name' => 'Overview' ), 'Foomo\Frontend') ?></li>
		<li><?= $view->partial('menuButton', array('url' => 'checkDialog', 'name' => 'Reset cache', 'parameters' => array('reset') ), 'Foomo\Frontend') ?></li>
		<li><?= $view->partial('menuButton', array('url' => 'checkDialog', 'name' => 'Populate fast cache', 'parameters' => array('populateFastCache') ), 'Foomo\Frontend') ?></li>
	</ul>
</nav>

