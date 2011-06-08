<? /* @var $view Foomo\MVC\View */ ?>
<nav id="menuSub">
	<ul>
		<li><?= $view->link('Browse', 'default'); ?></li>
		<li><?= $view->link('Create a new configuration', 'newConfEditor'); ?></li>
		<li><?= $view->link('Remove all old configurations', 'removeOldConfs'); ?></li>
	</ul>
</nav>
