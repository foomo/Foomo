<?php
/* @var $model Foomo\Config\Frontend\Model */


//var_dump($module, $domain, $subDomainName, $domainConfigClass);return;
$def = new $domainConfigClass;
$config = Foomo\Config::getCurrentConfYAML($module, $domain, $subDomain);

if(empty($subDomainName)){
	$subDomainName = '';
}


$delParms = array(
	'module' => $module,
	'domain' => $domain,
	'subDomain' => $subDomain
);
$aDefaultName = 'default-' . $module . '-' . $domain . '-' . $subDomain;
$aCurrentName = 'default-' . $module . '-' . $domain . '-' . $subDomain;

$oldConfigs = Foomo\Config\Utils::getOldConfigs();
$foundOldConfigs = array();
$oldOnes = array();
foreach($oldConfigs as $oldConfig) {
	/* @var $oldConfig \Foomo\Config\OldConfig */
	if($oldConfig->name == $domain && $oldConfig->module == $module && $oldConfig->domain == $subDomainName) {
		$foundOldConfigs[] = $oldConfig;
		$oldOnes[] = $oldConfig;
	}
}

$yamlRenderFunc = function($yaml) {
	if(class_exists('GeSHi')) {
		$geshi = new GeSHi($yaml, 'rails'); 
		return $geshi->parse_code();
	} else {
		return $view->escape($yaml);
	}
}

?>


<div class="tabBox">
	<div class="tabNavi">
		<ul>
			<li class="selected">Current</li>
			<li>Edit</li>
			<li>Default</li>
			<? if(count($oldOnes) > 0): ?><li>History</li><? endif; ?>
		</ul>
		<hr class="greyLine">
	</div>
	<div class="tabContentBox">
		
		<div class="tabContent tabContent-1 selected">
		
			<h2>Current</h2>
			<div class="tabBox">
				<div class="tabNavi">
					<ul>
						<li class="selected">Regular</li>
						<li>Dumped</li>
					</ul>
					<hr class="greyLine">
				</div>
				<div class="tabContentBox">
					<div class="tabContent tabContent-1 selected">
						<h2>Regular</h2>
						<div class="greyBox"><pre><?= call_user_func_array($yamlRenderFunc, array(\Foomo\Config::getConf($module, $domain, $subDomain))); ?></pre></div>
					</div>
					<div class="tabContent tabContent-2">
						<h2>Dumped</h2>
						<div class="greyBox"><pre><?= var_dump(\Foomo\Config::getConf($module, $domain, $subDomain)) ?></pre></div>
					</div>
				</div>
			</div>
			
		</div>
		
		<div class="tabContent tabContent-2">
			
			<h2>Edit value</h2>
			<div class="greyBox">
			<form action="<?= $view->url('actionSetConf'); ?>" method="post">
				<input type="hidden" name="module" value="<?= $module; ?>">
				<input type="hidden" name="domain" value="<?= $domain; ?>">
				<input type="hidden" name="subDomain" value="<?= $subDomain; ?>">
				<div class="formBox">
					<textarea rows="20" class="yamlEdit" name="yaml"><?= $config ?></textarea>
				</div>
				<div class="formBox">
					<input class="submitButton" type="submit" value="Update value">
				</div>
			</form>
			</div>
		
		</div>

		<div class="tabContent tabContent-3">
			
			<h2>Default value</h2>
			<div class="greyBox"><pre><?= call_user_func_array($yamlRenderFunc, array(\Foomo\Config::getDefaultConfig($domain))) ?></pre></div>
			
		</div>
		
		<? if(count($oldOnes) > 0): ?>
		
		<div class="tabContent tabContent-4">
			
			<h2>History</h2>

			<? foreach($oldOnes as $oldConfig): ?>

			<div class="toggleBox">
				<div class="toogleButton">
					<div class="toggleOpenIcon">+</div>
					<div class="toggleOpenContent"><?= date('Y-m-d H:i:s', $oldConfig->timestamp) ?></div>
				</div>
				<div class="toggleContent">

					<div class="greyBox"><pre><?= call_user_func_array($yamlRenderFunc, array(file_get_contents($oldConfig->filename))) ?></pre></div>

					<? $showOldConfId = 'old-' . $oldConfig->id; ?>
					
					<?= $view->link('Restore', 'restoreOldConf', array($oldConfig->id), array('class' => 'linkButtonYellow')); ?>
					<?= $view->link('Delete', 'deleteOldConf', array($oldConfig->id), array('class' => 'linkButtonRed')); ?>
					
				</div>
			</div>

			<? endforeach; ?>

		</div>
		
		<? endif; ?>
		

	</div>
</div>
