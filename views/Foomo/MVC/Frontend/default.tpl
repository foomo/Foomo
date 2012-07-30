<?php

/* @var $model Foomo\MVC\Frontend\Model */
/* @var $view Foomo\MVC\View */

?>
<h2>Create a Foomo MVC App</h2>

<form method="post" action="<?= $view->escape($view->url('create')) ?>">
	<div class="greyBox">
		<div class="formBox">
			<div class="formTitle">Module</div>
			<select name="module">
				<? foreach(\Foomo\Modules\Manager::getEnabledModules() as $enabledModule): ?>
				<option><?= $view->escape($enabledModule) ?></option>
				<? endforeach; ?>
			</select>
		</div>
		<div class="formBox">	
			<div class="formTitle">Namespace</div>
			<input type="text" name="namespace">
		</div>
		<div class="formBox">
			<div class="formTitle">Author</div>
			<input type="text" name="author" value="<?= $view->escape($_SERVER['PHP_AUTH_USER']?$_SERVER['PHP_AUTH_USER']:'') ?>">
		</div>
		<div class="formBox">
			<input class="submitButton" type="submit" value="create">
		</div>
	</div>
</form>