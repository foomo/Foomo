<?php
/* @var $view \Foomo\MVC\View */
/* @var $model Foomo\BasicAuth\Frontend\Model */
use Foomo\BasicAuth\Utils;
?>
<div id="main">
	<?= $view->partial('menu') ?>
	<div id="appContent">
		<h3>Create a new authentication domain</h3>
		<form action="<?= $view->escape($view->url('createDomain')) ?>" method="post">
			<label>name of the new domain</label>
			<input type="text" name="domain">
			<input type="submit" value="create new domain">
		</form>
	</div>
</div>
