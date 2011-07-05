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
	
		<? if(!empty($subDomainName)): ?>
				<h3><?= $subDomainName ?></h3>
		<? endif; ?>
	
		<? foreach($subDomainConfigs as $domain => $file): 
			$domainConfigClass = Foomo\Config::getDomainConfigClassName($domain);
			$displayThisConfig = $model->showConfigModule == $module && $model->showConfigDomain == $domain && $model->showConfigSubDomain == $subDomainName;
			$editorId = str_replace('.', '-', $module . '-' . $domain . '-' . $subDomainName);
		?>	

		<div class="toggleBox">
			<div class="toogleButton">
				<div class="toggleOpenIcon">+</div>
				<div class="toggleOpenContent"><?= (!empty($subDomainName)? $subDomainName . '/': '') . $domain ?></div>
			</div>
			<div class="toggleContent">
				
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
				
				<?= $view->partial('edit', array('domain' => $domain, 'module' => $module, 'subDomain' => $subDomainName, 'domainConfigClass' => $domainConfigClass)) ?>
				
				<?= $view->link('Delete current', 'deleteConf', array($module, $domain, $subDomainName), array('class' => 'linkButtonRed')); ?>

			</div>
		</div>
		
		<? endforeach; ?>
		
		<? endforeach; ?>
		<br>
	<? endforeach; ?>
	
	<? if(count($foundOldConfigs) < count($oldConfigs)): ?>
		
		<br>
		<hr>
		<br>
		<div class="rightBox">
			<?= $view->link('Delete all old configurations', 'removeOldConfs', array(), array('class' => 'linkButtonRed')); ?>
		</div>
		<h2>Trash</h2>
		
		<? foreach($oldConfigs as $oldConfig): ?>

			<? if(!in_array($oldConfig, $foundOldConfigs)):
				//$showOldConfId = $view->escape(str_replace('.' , '-', 'oldConf-' . $oldConfig->id ));
			?>
			<div class="toggleBox">
				<div class="toogleButton">
					<div class="toggleOpenIcon">+</div>
					<div class="toggleOpenContent"><?=	$oldConfig->module . '/' . (($oldConfig->domain != '')?$oldConfig->domain . '/':'') . $oldConfig->name ?> (<?= date('Y-m-d H:i:s', $oldConfig->timestamp) ?>)</div>
				</div>
				<div class="toggleContent">

					<div class="greyBox"><pre><?= $view->escape(file_get_contents($oldConfig->filename)) ?></pre></div>

					<? $showOldConfId = 'old-' . $oldConfig->id; ?>
					
					<?= $view->link('Restore', 'restoreOldConf', array($oldConfig->id), array('class' => 'linkButtonYellow')); ?>
					<?= $view->link('Delete', 'deleteOldConf', array($oldConfig->id), array('class' => 'linkButtonRed')); ?>
					
				</div>
			</div>
			<? endif; ?>

		<? endforeach; ?>	


	<? endif; ?>


</div>
