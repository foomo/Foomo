<? $https = isset($_SERVER['HTTPS']); ?>
<header>
    <div id="infoTop">
		<div>
			Server: <b>
						<span 
							class="<?= $https?'textGreen':'textRed' ?>" 
							title="<?= $https?'https is always good':'you should use https' ?>"
						><?= $https?'https':'http' ?></span>://<?= parse_url(Foomo\Utils::getServerUrl(), PHP_URL_HOST) ?>
					</b> 
			IP: <?= $_SERVER['SERVER_ADDR'] ?> Port: <?= $_SERVER['SERVER_PORT'] ?>
		</div>
		<div>Mode: <b><?= Foomo\Config::getMode() ?></b></div>
	</div>
    <div id="menuTop">

		<div><?= $view->link('Logout', 'logout') ?></div>
		<div>
			<?= $view->partial("greetUser") ?>
		</div>

	</div>
    <div id="logoBox"><a href="<?= \htmlspecialchars($view->url('default', array())) ?>"><img src="<?= $view->asset('img/site/foomo-logo.png') ?>"></a><div id="version">toolbox v 1.02</div></div>
</header>
