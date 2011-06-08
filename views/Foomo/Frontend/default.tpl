<?= $view->partial('header') ?>
<?= $view->partial('menu') ?>

<?php
$coreModules = array(\Foomo\Module::NAME, 'Foomo.TestRunner', 'Foomo.Services', 'Foomo.Docs' );
$enabledModules = Foomo\Modules\Manager::getEnabledModules();
$moduleLinks = array();
foreach($enabledModules as $enabledModuleName) {
	if(Foomo\Modules\Manager::moduleHasFrontEnd($enabledModuleName)) {
		$moduleLinks[$enabledModuleName] = '<li><a href="modules/' . $enabledModuleName . '/index.php" title="' . Foomo\Modules\Manager::getModuleDescription($enabledModuleName) . '">'.$enabledModuleName.'</a></li>';
	}
}
?>
<div id="fullContent">
	<h1>
	<?
		$hour = date('H');
		$user = $_SERVER['PHP_AUTH_USER'];
		switch(true) {
			case ($hour < 10 && $hour > 6):
				echo 'Good morning ' . $user . ' !';
				break;
			case ($hour > 12 && $hour < 13):
				echo 'You should be having lunch ' . $user . ' and I am hungry as well';
				break;
			case ($hour > 20 && $hour < 24):
				echo 'Is it not a little late to work ' . $user  . ' ?';
				break;
			case ($hour > 0 && $hour < 6):
				echo 'Somebody launching tonight ' . $user  . ' ?';
				break;
			default:
				echo 'Hello ' . $user . ' !';
		}
	?>
	</h1>
	<h2>Shortcuts</h2>
	<ul>
		<li><?= $view->link('Reset the autoloader', 'resetAutoloader', array(), 'when you write new classes you need to reset the autoloader in order to use them'); ?></li>
		<li><a title="wanna start over?" href="<?= \Foomo\ROOT_HTTP . '/setup.php' ?>">Setup</a></li>
		<? if(Foomo\Session::getConf() && Foomo\Session::getConf()->type == 'foomo'): ?>
			<li><a href="<?= \Foomo\ROOT_HTTP . '/sessionGc.php' ?>">collect RadSession garbage</a></li>
		<? endif; ?>
		<li><a title="may be your life saver" href="<?= \Foomo\ROOT_HTTP . '/hiccup.php' ?>">Hiccup</a> &lt;-- you may want to bookmark that one</li>
	</ul>
	<?= $model->classMap ?>
</div>
<?= $view->partial('footer') ?>
