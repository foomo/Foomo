<?php

/* @var $model Foomo\Setup\Controller */

$defaultAuthFilename = \Foomo\BasicAuth::getDefaultAuthFilename();
if(file_exists($defaultAuthFilename)) {
	$adminAuthExists = true;
} else {
	$adminAuthExists = false;
}

// @todo add some checks
// php version
// php extensions
// pear stuff


?>

<style type="text/css">

body {
	font-family: "Courier New";
}

table {
	width: 100%;
	border-collapse: collapse;
}

thead {
	background-color: lightgrey;
}

tr, td {
	border: 1px grey solid;
}

.docs {
	font-size: 10px;
}
.ok {
	color: green;
}
.notOk {
	color:red;
}

</style>

<h1>Setup foomo on a green field</h1>
<ul>
	<li><a href="#env">Take a look at the environment</a></li>
	<li><a href="#resources">Check resources</a></li>
	<? if(!$adminAuthExists): ?>
		<li><a href="#adminUser">Setup an admin user</a></li>
	<? else: ?>
		<li>Admin user exists, well at least the auth file exists</li>
	<? endif; ?>
	<li><a href="#webserver">Webserver config</a></li>
</ul>

<!-- Environment -->

<a name="env">
	<h2>Environment</h2>
</a>
<?
echo __DIR__ . '/../Info/Frontend/partials/foomoInfo.tpl';
include __DIR__ . '/../Info/Frontend/partials/foomoInfo.tpl';
?>
<!-- Resources -->

<a name="resources">
	<h2>Resource status</h2>
</a>

<h3>Cli Shell</h3>
<? if(file_exists(\Foomo\Setup::getShellFilename())): ?>
	<p class="ok"><?= htmlspecialchars(\Foomo\Setup::getShellFilename()) ?></p>
<? else: ?>
	<p class="ok">looks like <?= htmlspecialchars(\Foomo\Setup::getShellFilename()) ?> could not be created</p>
<? endif; ?>
<h3>Filesystem</h3>
<pre>

<?
foreach(array('config' => 'Foomo\CORE_CONFIG_DIR_CONFIG','var' => '\Foomo\CORE_CONFIG_DIR_VAR') as $dir => $const):
	$dir = constant($const);
	$problem = !file_exists($dir) || !is_writable($dir) || !is_dir($dir);
?>
<span class="<?= $problem?'notOk':'ok' ?>">directory <?= $dir ?> (from <?= $const ?>) :</span>

<? if($problem): ?>

  There are problems :

<?= !file_exists($dir)?'    folder does not exist' . PHP_EOL :'' ?>
<?= !is_dir($dir)?'    is not a folder' . PHP_EOL:'' ?>
<?= !is_writable($dir)?'    can not write to the folder' . PHP_EOL:'' ?>

<? else: ?>
ok
<? endif; ?>


<? endforeach; ?>
</pre>
<? if(!empty($model->error)): ?>
	<i><?= $model->error ?></i>
<? endif; ?>


<!-- Setup -->

<?

$basicAuthFoldername = dirname($defaultAuthFilename);

if(file_exists($basicAuthFoldername)) {
	$adminAuthPossible = true;
} else {
	$adminAuthPossible = false;
}

?>

<a name="adminUser">
	<h2>Setup an admin user</h2>
</a>
<? if(!$adminAuthPossible): ?>
	<p>
		There is no runmode set yet, or the var folders have not been generated - take a look at the env and the resources, once those are all fine, then come back to here.
	</p>
<? elseif($adminAuthPossible && !$adminAuthExists): ?>
	<form action="<?= \Foomo\MVC\ControllerHelper::staticRenderAppLink('Foomo\Setup\Controller', 'actionSetupAdminUser') ?>" method="POST">

		<label>admin user name</label>
		<br>
		<input name="userName" type="text">
		<br>

		<label>password</label>
		<br>
		<input name="password" type="password">
		<br>

		<label>repeat password</label>
		<br>
		<input name="passwordRepeat" type="password">
		<br>

		<input type="submit" value="setup admin user">

	</form>
<? else: ?>
	<p>Well, it looks like you are all set. <a href="index.php">proceed to the toolbox</a></p>
<? endif; ?>
