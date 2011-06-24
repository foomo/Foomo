<header>
    <div id="infoTop">
		<div>Server: <b><?= parse_url(Foomo\Utils::getServerUrl(), PHP_URL_HOST) ?></b></div>
		<div>Mode: <b><?= Foomo\Config::getMode() ?></b></div>
	</div>
    <div id="menuTop">
		<div><a href="" target="_blank">Logout</a></div>
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
    <div id="logoBox"><img src="<?= \Foomo\ROOT_HTTP ?>/img/site/foomo-logo.png"><div id="version">toolbox v 1.02</div></div>
</header>
