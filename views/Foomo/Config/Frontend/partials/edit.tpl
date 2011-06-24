<?php
/* @var $model Foomo\Config\Frontend\Model */


//var_dump($module, $domain, $subDomainName, $domainConfigClass);return;
$def = new $domainConfigClass;
$config = Foomo\Config::getCurrentConfYAML($module, $domain, $subDomain);
$delParms = array(
	'module' => $module,
	'domain' => $domain,
	'subDomain' => $subDomain
);
$aDefaultName = 'default-' . $module . '-' . $domain . '-' . $subDomain;
$aCurrentName = 'default-' . $module . '-' . $domain . '-' . $subDomain;
?>
<ul>
	<li><a href="#<?= $aCurrentName ?>">Current value</a></li>
	<li><a href="#<?= $aDefaultName ?>">Default value</a></li>
	<li><?= $view->link('restore default', 'restoreDefault', array($module, $domain, $subDomain)) ?></li>
</ul>
<div>
    <form action="<?= $view->url('actionSetConf'); ?>" method="post">
		<input type="hidden" name="module" value="<?= $module; ?>">
		<input type="hidden" name="domain" value="<?= $domain; ?>">
		<input type="hidden" name="subDomain" value="<?= $subDomain; ?>">
		<p>current</p>
		<textarea
			rows="20"
			class="yamlEdit"
			name="yaml"
			><?= $config ?></textarea>
		<p>
			<input type="submit" value="update">
		</p>
    </form>
</div>

<h3><a name="<?= $aCurrentName ?>">Current value</a></h3>
<div><pre><?= var_dump(\Foomo\Config::getConf($module, $domain, $subDomain)) ?></pre></div>

<h3><a name="<?= $aDefaultName ?>">Default value</a></h3>

<div><pre><?= $view->escape(\Foomo\Config::getDefaultConfig($domain)) ?></pre></div>
