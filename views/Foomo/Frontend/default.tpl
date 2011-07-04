<div id="page">
	<?= $view->partial('header') ?>
	<?= $view->partial('menu') ?>


	<?php
	/*
	$coreModules = array(\Foomo\Module::NAME, 'Foomo.TestRunner', 'Foomo.Services', 'Foomo.Docs' );
	$enabledModules = Foomo\Modules\Manager::getEnabledModules();
	$moduleLinks = array();
	foreach($enabledModules as $enabledModuleName) {
		if(Foomo\Modules\Manager::moduleHasFrontEnd($enabledModuleName)) {
			$moduleLinks[$enabledModuleName] = '<li><a href="modules/' . $enabledModuleName . '/index.php" title="' . Foomo\Modules\Manager::getModuleDescription($enabledModuleName) . '">'.$enabledModuleName.'</a></li>';
		}
	}
	*/
	?>
	<div id="fullContent">
		<h1>
		<?
			$hour = date('H');
			$user = $_SERVER['PHP_AUTH_USER'];
			switch(true) {
				case ($hour < 10 && $hour > 6):
					$key = 'GREET_GOOD_MORNING';
					break;
				case ($hour > 12 && $hour < 13):
					$key = 'GREET_LUNCH';
					break;
				case ($hour > 20 && $hour < 24):
					$key = 'GREET_LATE';
					break;
				case ($hour > 0 && $hour < 6):
					$key = 'GREET_LAUNCH';
					break;
				default:
					$key = 'GREET_DEFAULT';
			}
			printf($view->_($key), $view->escape($user));
		?>
		</h1>
		<h2>Shortcuts</h2>
		<ul>
			<li><?= $view->link('Reset the autoloader', 'resetAutoloader', array(), array('title' => 'when you write new classes you need to reset the autoloader in order to use them')); ?></li>
			<li><a title="wanna start over?" href="<?= \Foomo\ROOT_HTTP . '/setup.php' ?>">Setup</a></li>
			<? if(Foomo\Session::getConf() && Foomo\Session::getConf()->type == 'foomo'): ?>
				<li><a href="<?= \Foomo\ROOT_HTTP . '/sessionGc.php' ?>">collect RadSession garbage</a></li>
			<? endif; ?>
			<li><a title="may be your life saver" href="<?= \Foomo\ROOT_HTTP . '/hiccup.php' ?>">Hiccup</a> &lt;-- you may want to bookmark that one</li>
		</ul>
		<?= $model->classMap ?>
	</div>
	<?= $view->partial('footer') ?>
</div>

