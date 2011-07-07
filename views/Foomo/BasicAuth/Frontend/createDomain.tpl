<?php
/* @var $view \Foomo\MVC\View */
/* @var $model Foomo\BasicAuth\Frontend\Model */
use Foomo\BasicAuth\Utils;
?>
<div id="main">
	<?= $view->partial('menu') ?>
	<div id="appContent">
		<form action="<?= $view->escape($view->url('createDomain')) ?>" method="post">
			<div class="greyBox">

				<div class="formBox">	
					<div class="formTitle">Name of the new domain</div>
					<input type="text" name="domain">
				</div>

				<div class="formBox">
					<input class="submitButton" type="submit" value="Create new domain"/>
				</div>

			</div>
		</form>
	</div>
</div>
