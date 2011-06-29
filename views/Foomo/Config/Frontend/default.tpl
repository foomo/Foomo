<?
/* @var $model Foomo\Config\Frontend\Model */
/* @var $view \Foomo\MVC\View */
$configs = Foomo\Config\Utils::getConfigs();
$oldConfigs = Foomo\Config\Utils::getOldConfigs();
$foundOldConfigs = array();
?>
<?= $view->partial('menu') ?>
<div id="appContent">
	
	<div id="config">

			<? foreach($configs as $module => $moduleConfigs): ?>
				<div class="whiteBox">
					<h2><?= $module ?></h2>
					<br>
					<div>
					
					<? foreach($moduleConfigs as $subDomainName => $subDomainConfigs): ?>
						
						<? if(!empty($subDomainName)): ?>
								<h3><?= $module ?> <?= $subDomainName ?></h3>
						<? endif; ?>
						
						<? foreach($subDomainConfigs as $domain => $file): 
							$domainConfigClass = Foomo\Config::getDomainConfigClassName($domain);
							$displayThisConfig = $model->showConfigModule == $module && $model->showConfigDomain == $domain && $model->showConfigSubDomain == $subDomainName;
							$editorId = str_replace('.', '-', $module . '-' . $domain . '-' . $subDomainName)
						?>		
								<div id="moduleBox">
									<div id="moduleItem">
										<b><?= $module . (!empty($subDomainName)?'/' . $subDomainName . '/':'/') . $domain ?></b>

										<ul id="ctrlButtons">
											<li><?= $view->partial('buttonYellow', array('url' => '', 'name' => 'Edit', 'js' => 'onclick="$(\'#'. $editorId .'\').toggle(300)"'  ), 'Foomo\Frontend') ?></li>
											<li><?= $view->partial('buttonYellow', array('url' => 'deleteConf', 'name' => 'Delete' , 'parameters' => array($module, $domain, $subDomainName)), 'Foomo\Frontend') ?></li>
										</ul>
									</div>

								
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
								</div>
								
								<? if(count($moduleConfigs) > 1 ): ?>
								<hr>
								<? endif; ?>
								
						<? endforeach; ?>
								

								
					<? endforeach; ?>
					</div>
				</div>
			<? endforeach; ?>
		
		
		<? if(count($foundOldConfigs) < count($oldConfigs)): ?>

			<br>
			<br>
			<div class="whiteBox">
				<h2>Old configurations</h2>
				<br>
			<? foreach($oldConfigs as $oldConfig): ?>
				<div id="moduleBox">
				<? if(!in_array($oldConfig, $foundOldConfigs)):
					$showOldConfId = $view->escape(str_replace('.' , '-', 'oldConf-' . $oldConfig->id ));
				?>
					<div id="moduleHistoryItem">
					<span><?=	$oldConfig->module . '/' . (($oldConfig->domain != '')?$oldConfig->domain . '/':'') . $oldConfig->name ?> ( <?= date('Y-m-d H:i:s', $oldConfig->timestamp) ?> )</span>
					<?= $view->partial('old', array('oldConfig' => $oldConfig)) ?>
					</div>
				<? endif; ?>
				</div>
			<? endforeach; ?>
				<br>
				<?= $view->link('Delete all old configurations', 'removeOldConfs'); ?>
			</div>
			
			
		
		<? endif; ?>
		
	</div>
</div>
