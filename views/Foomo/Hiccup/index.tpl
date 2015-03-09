<h1>Hiccup <small><i>a bookmark may be very helpful ...</i></small></h1>
<h2><a href="<?= \Foomo\ROOT_HTTP ?>/index.php">back to the toolbox</a></h2>
<p>
If you have broken your configuration so much, that the toolbox will not come back up, then this is the right place to come after you have fixed things. 
</p>
<? 
	$status = Foomo\Hiccup::getStatus();
	if(!empty($status)):
?>
<h2>
<pre>Foomo\Config info

<?
echo 'runMode   : ' . Foomo\Config::getMode() . PHP_EOL;
echo 'config    : ' . Foomo\Config::getConfigDir() . PHP_EOL;
echo 'var       : ' . Foomo\Config::getVarDir() . PHP_EOL;
?>
</pre>
</h2>
<h2>things seem to be fine</h2>
<pre><? echo htmlspecialchars(Foomo\Yaml::dump($status));?></pre>
<? else:?>
<h2>looks like you need me - the server status could not be retrieved</h2>
<? endif;?>
<h2>So what do you want to hiccup?</h2>
<ul>
	<li><a href="<?= \Foomo\MVC\ControllerHelper::staticRenderAppLink('Foomo\Hiccup\Controller', 'actionResetAutoloader') ?>">remove autoloader cache</a></li>
	<li><a href="<?= \Foomo\MVC\ControllerHelper::staticRenderAppLink('Foomo\Hiccup\Controller', 'actionResetConfigCache') ?>">remove config cache</a></li>
	<li><a href="<?= \Foomo\MVC\ControllerHelper::staticRenderAppLink('Foomo\Hiccup\Controller', 'actionDisableAllModules') ?>">disable all modules</a></li>
	<li><a href="<?= \Foomo\MVC\ControllerHelper::staticRenderAppLink('Foomo\Hiccup\Controller', 'actionResetEverything') ?>">reset everything</a></li>
</ul>
