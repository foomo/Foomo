<?php

/* @var $model Foomo\BasicAuth\HTML\Frontend\Model */
/* @var $view Foomo\MVC\View */

?>
<h1>Authentication required</h1>
<form method="post" action="<?= $view->escape($_SERVER["REQUEST_URI"]) ?>">
	<div>
		<label>name</label><br>
		<input name="name" placeholder="name">
	</div>
	<div>
		<label>password</label><br>
		<input name="password" placeholder="password" type="password">
	</div>
	<div>
		<input type="submit" value="login">
	</div>

</form>