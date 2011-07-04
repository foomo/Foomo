<?= $view->partial('menu') ?>
<div id="appContent">
	<form action="<?= $view->url('actionCreateModule') ?>" method="POST">
		<div class="greyBox">
		
			<div class="formBox">	
				<div class="formTitle">Name</div>
				<input type="text" name="name">
			</div>
			
			<div class="formBox">	
				<div class="formTitle">Description</div>
				<textarea name="description"></textarea>
			</div>
			
			<div class="formBox">	
				<div class="formTitle">Required modules ( comma separated )</div>
				<textarea name="requiredModules"></textarea>
			</div>
			
			<div class="formBox">
				<input class="submitButton" type="submit" value="Create Module"/>
			</div>
		
		</div>
	</form>	
</div>
