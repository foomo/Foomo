<?= $view->partial('menu') ?>
<div id="appContent">
	<form method="POST" action="<?= $view->url('actionCreateConf') ?>">
		<p>Module</p>
		<p>
			<select name="module">
				<? foreach(Foomo\Modules\Manager::getEnabledModules() as $moduleName): ?>
					<option><?= $moduleName ?></option>
				<? endforeach; ?>
			</select>
		</p>
		<p>Module subdomain (optional)</p>
		<p><input type="text" name="subDomain"></p>
		<p>domain</p>
		<p>
			<select name="domain">
				<? foreach(Foomo\Config\Utils::getAllDomainConfigClasses() as $domain => $className): ?>
					<option value="<?= $domain ?>"><?= $domain . ' - ' . $className; ?></option>
				<? endforeach; ?>
			</select>
		</p>
		<p><input type="submit" value="create"/></p>
	</form>
</div>
