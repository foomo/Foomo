<?php
/* @var $view \Foomo\MVC\View */
/* @var $model Foomo\BasicAuth\Frontend\Model */
use Foomo\BasicAuth\Utils;
?>
<?= $view->partial('menu') ?>
<div id="appContent">
	<? if(!$model->currentAuthDomain): ?>
		<h3>Create a new authentication domain</h3>
		<form action="<?= $view->escape($view->url('createDomain')) ?>" method="post">
			<label>name of the new domain</label>
			<input type="text" name="domain">
			<input type="submit" value="create new domain">
		</form>
		<h3>Existing authentication domains</h3>
		<ul>
			<? foreach(Utils::getDomains() as $domain): ?>
				<li>
					<?= $view->link($domain, 'showDomain', array($domain)) ?> -
					<?= $view->link('delete', 'deleteDomain', array($domain)) ?>
				</li>
			<? endforeach; ?>
		</ul>
	<? else: ?>
		<h3>Domain <?= $view->escape($model->currentAuthDomain) ?></h3>
		<h4>Create a new user in domain <?= $view->escape($model->currentAuthDomain) ?></h4>
		<form action="<?= $view->escape($view->url('updateUser')) ?>" method="post">
			<input type="hidden" name="domain" value="<?= $view->escape($model->currentAuthDomain) ?>">
			<label>name of the new user</label>
			<input type="text" name="user">
			<label>password</label>
			<input type="password" name="password" value="">
			<input type="submit" value="create new user">
		</form>
		<h4>Existing users</h4>
		<ul>
			<? foreach(Utils::getUsers($model->currentAuthDomain) as $user => $hash): ?>
				<li>
					<?= $view->escape($user) ?> - <?= $view->link('delete', 'deleteUser', array($model->currentAuthDomain, $user)) ?>
					<form action="<?= $view->escape($view->url('updateUser')) ?>" method="post">
						<input type="hidden" name="domain" value="<?= $view->escape($model->currentAuthDomain) ?>">
						<input type="hidden" name="user" value="<?= $view->escape($user) ?>">
						<input type="password" name="password">
						<input type="submit" value="set password">
					</form>
				</li>
			<? endforeach; ?>
		</ul>
	<? endif; ?>
</div>
