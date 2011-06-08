<div>
	<h1>Setup status information</h1>
	<h2>alias</h2>
	<div style="padding-left:20px">
		<?
		//check if alias is set
		if ($_SERVER['REQUEST_URI'] == \Foomo\ROOT_HTTP .  '/hiccup.php'): ?>
			<p class="ok">alias setup is correct</p>
		<? else : ?>
			<p class="error">alias setup is wrong. add the following code to your vhost-configuration:</p>
		<?
			$output = 'Alias /r/ '.\Foomo\ROOT.'/htdocs/';
		?><p><?= $output ?></p>
		<? endif; ?>
</div>
<h2>auto_prepend_file</h2>
<div style="padding-left:20px">
<? if (!is_null(ini_get('auto_prepend_file')) && defined('\Foomo\ROOT')): ?>
	<p class="ok">auto_prepend_file correctly loaded</p>
<? else: ?>
	<p class="error">no auto_prepend_file found. add the following code to your vhost-configuration:</p>
	<p>&lt;Directory <?= ((DIRECTORY_SEPARATOR == '\\') ? str_replace('\\','/',\Foomo\ROOT) : \Foomo\ROOT) ?>&gt;<br />
	php_admin_value 'auto_prepend_file' <?= ((DIRECTORY_SEPARATOR == '\\') ? str_replace('\\','/',\Foomo\ROOT) : \Foomo\ROOT) . '/' . "lib" . '/' . "foomo.inc.php" ?><br />
				&lt/Directory&gt;";
<? endif; ?>
</div>
<h2>directories</h2>
<div style="padding-left:20px">
<?
$directories = array(
	\Foomo\CORE_CONFIG_DIR_CONFIG,
	\Foomo\CORE_CONFIG_DIR_VAR
);
foreach ($directories as $directory): ?>
	<? if (file_exists($directory) && is_dir($directory) && is_writeable($directory)):?>
		<h3 class="ok"><?= $directory ?></h3>
		<p class="ok">directory is writeable</p>
	<? else: ?>
		<h3 class="error"><?= $directory ?></h3>
		<p class="error">directory is not writeable, please edit directory permissions</p>
	<? endif; ?>
<? endforeach; ?>
</div>
<h2>auth files</h2>
<div style="padding-left:20px">
<?
	foreach ( array(
				\Foomo\CORE_CONFIG_DIR_VAR . DIRECTORY_SEPARATOR. 'test' .DIRECTORY_SEPARATOR.'basicAuth'.DIRECTORY_SEPARATOR.'default',
				\Foomo\CORE_CONFIG_DIR_VAR . DIRECTORY_SEPARATOR. 'development' .DIRECTORY_SEPARATOR.'basicAuth'.DIRECTORY_SEPARATOR.'default',
				\Foomo\CORE_CONFIG_DIR_VAR . DIRECTORY_SEPARATOR. 'production' .DIRECTORY_SEPARATOR.'basicAuth'.DIRECTORY_SEPARATOR.'default'
		) as $file): ?>
		<? if (file_exists($file)): ?>
			<h3 class="ok"><?= $file ?></h3>
			<p class="ok">auth-file exists</p>
		<? else: ?>
			<h3 class="error"><?= $file ?></h3>
			<p class="error">auth-file does not exist</p>
		<? endif; ?>
	<? endforeach; ?>
	</div>
</div>
