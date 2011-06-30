<?
/* @var $model Foomo\Config\Frontend\Model */
/* @var $view \Foomo\MVC\View */
$configs = Foomo\Config\Utils::getConfigs();
$oldConfigs = Foomo\Config\Utils::getOldConfigs();
$foundOldConfigs = array();
?>
<?= $view->partial('menu') ?>
<div id="appContent">


	<? foreach($configs as $module => $moduleConfigs): ?>
	
	<h2><?= $module ?></h2>
	
		<? foreach($moduleConfigs as $subDomainName => $subDomainConfigs): ?>
	
		<? foreach($subDomainConfigs as $domain => $file): 
			$domainConfigClass = Foomo\Config::getDomainConfigClassName($domain);
			$displayThisConfig = $model->showConfigModule == $module && $model->showConfigDomain == $domain && $model->showConfigSubDomain == $subDomainName;
			$editorId = str_replace('.', '-', $module . '-' . $domain . '-' . $subDomainName);
		?>	

		<div class="toggleBox">
			<div class="toogleButton">
				<div class="toggleOpenIcon">+</div>
				<div class="toggleOpenContent">
					<?= $module . (!empty($subDomainName)?'/' . $subDomainName . '/':'/') . $domain ?>
				</div>
			</div>
			<div class="toggleContent">
				<ul id="ctrlButtons">
					<li><?= $view->partial('buttonYellow', array('url' => '', 'name' => 'Edit', 'js' => 'onclick="$(\'#'. $editorId .'\').toggle(300)"'  ), 'Foomo\Frontend') ?></li>
					<li><?= $view->partial('buttonYellow', array('url' => 'deleteConf', 'name' => 'Delete' , 'parameters' => array($module, $domain, $subDomainName)), 'Foomo\Frontend') ?></li>
				</ul>
				<div class="detail" id="<?= $editorId ?>" style="display:<?= $displayThisConfig?'block':'none' ?>">
					<?= $view->partial('edit', array('domain' => $domain, 'module' => $module, 'subDomain' => $subDomainName, 'domainConfigClass' => $domainConfigClass)) ?>
				</div>
				
				<? 
				$oldOnes = array();
				foreach($oldConfigs as $oldConfig) {
					/* @var $oldConfig \Foomo\Config\OldConfig */
					if($oldConfig->name == $domain && $oldConfig->module == $module && $oldConfig->domain == $subDomainName) {
						$foundOldConfigs[] = $oldConfig;
						$oldOnes[] = $oldConfig;
					}
				}
				?>

				<? if(count($oldOnes) > 0): ?>

					<? foreach($oldOnes as $oldConfig): ?>
					<div id="moduleHistoryItem">
					<span><?= date('Y-m-d H:i:s', $oldConfig->timestamp) ?></span>

					<?= $view->partial('old', array('oldConfig' => $oldConfig)) ?>
					</div>
					<? endforeach; ?>


				<? endif; ?>
				
				<hr>

				
				Ut enim ad minim veniam, quis nostrud exerc. Irure dolor in reprehend incididunt ut labore et dolore magna aliqua.<br>
				Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse molestaie cillum.
			</div>
		</div>
		<? endforeach; ?>
		<? endforeach; ?>
	
	<? endforeach; ?>
	


</div>
