<?
/* @var $view Foomo\MVC\View */
/* @var $model Foomo\Frontend\Model */
?>
<nav id="menuMain">
	<ul>
		<li><?= $view->link('Home', 'default') ?></li>
		<li><?= $view->link('Configuration', 'config') ?></li>
		<li><?= $view->link('Modules', 'modules') ?></li>
		<li><?= $view->link('Log', 'log') ?></li>
		<li><?= $view->link('Info', 'info') ?></li>
		<li><?= $view->link('Auth', 'basicAuth') ?></li>
		<li><?= $view->link('Cache', 'cache') ?></li>
	</ul>
</nav>
