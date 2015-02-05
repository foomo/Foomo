<?php

/* @var $model Foomo\BasicAuth\HTML\Frontend\Model */
/* @var $view Foomo\MVC\View */

\Foomo\HTMLDocument::getInstance()
	->addStylesheets([
		Foomo\Module::getHtdocsBuildPath('css/reset.css'),
		//Foomo\Module::getHtdocsBuildPath('css/module.css'),
		Foomo\Module::getHtdocsBuildPath('css/auth.css')
	])
;
?>
<form method="post" action="<?= $view->escape($_SERVER["REQUEST_URI"]) ?>">
	<h1>Authentication required</h1>
	<label>name</label><br>
	<input name="name" placeholder="name" value="<?= $view->escape($model->user) ?>">
	<label>password</label><br>
	<input
		class="<?= !empty($model->password) ? 'retry' : '' ?>"
		name="password"
		placeholder="password"
		type="password"
		value="<?= $view->escape($model->password) ?>"
	>
	<input type="submit" value="Login">
</form>
