<? /* @var $view Foomo\MVC\View */ ?>
<nav id="menuSub">
	<ul>
		<li><?= $view->link('Overview', 'default'); ?></li>
		<li><?= $view->link('Create a new configuration', 'newConfEditor'); ?></li>
		<li><?= $view->link('Delete all old configurations', 'removeOldConfs'); ?></li>
	</ul>
</nav>
