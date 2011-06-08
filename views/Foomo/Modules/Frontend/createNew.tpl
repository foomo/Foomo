<?= $view->partial('menu') ?>
<div id="appContent">
	<form action="<?= $view->url('actionCreateModule') ?>" method="POST">
		<label>name</label>
		<p>
			<input type="text" name="name">
		</p>
		<label>description</label>
		<p>
			<textarea name="description"></textarea>
		</p>
		<label>required modules - comma separated</label>
		<p>
			<textarea name="requiredModules"></textarea>
		</p>
		<input type="submit" name="submit" value="create">
	</form>
</div>
