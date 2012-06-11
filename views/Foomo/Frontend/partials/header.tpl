<header>
    <div id="infoTop">
		<div>Server: <b><?= parse_url(Foomo\Utils::getServerUrl(), PHP_URL_HOST) ?></b></div>
		<div>Mode: <b><?= Foomo\Config::getMode() ?></b></div>
	</div>
    <div id="menuTop">

		<div><?= $view->link('Logout', 'logout') ?></div>
		<div>
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
		</div>

	</div>
    <div id="logoBox"><a href="<?= \htmlspecialchars($view->url('default', array())) ?>"><img src="<?= $view->asset('img/site/foomo-logo.png') ?>"></a><div id="version">toolbox v 1.02</div></div>
</header>
