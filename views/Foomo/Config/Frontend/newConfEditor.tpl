<?= $view->partial('menu') ?>
<div id="appContent">
	
	<form method="POST" action="<?= $view->url('actionCreateConf') ?>">
		<div class="greyBox">
			
			<div class="formBox">	
				<div class="formTitle">Module</div>
				<select name="module">
					<? foreach(Foomo\Modules\Manager::getEnabledModules() as $moduleName): ?>
						<option><?= $moduleName ?></option>
					<? endforeach; ?>
				</select>
			</div>
		
			<div class="formBox">
				<div class="formTitle">Module subdomain (optional)</div>
				<input type="text" name="subDomain">
			</div>
			
			<div class="formBox">
				<div class="formTitle">Domain</div>
				<select name="domain">
					<? foreach(Foomo\Config\Utils::getAllDomainConfigClasses() as $domain => $className): ?>
						<option value="<?= $domain ?>"><?= $domain . ' - ' . $className; ?></option>
					<? endforeach; ?>
				</select>
			</div>
			<div class="formBox">
				<input class="submitButton" type="submit" value="Create Configuration"/>
			</div>
			
		</div>
	</form>
	
</div>
