<?
/* @var $model Foomo\Config\Frontend\Model */
/* @var $view \Foomo\MVC\View */
$configs = Foomo\Config\Utils::getConfigs();
$oldConfigs = Foomo\Config\Utils::getOldConfigs();
$foundOldConfigs = array();
?>
<?= $view->partial('menu') ?>
<div id="appContent">
	<div id="configManagerList">
		<ul>
			<? foreach($configs as $module => $moduleConfigs): ?>
				<li>
					<?= $module ?>
					<ul>
					<? foreach($moduleConfigs as $subDomainName => $subDomainConfigs): ?>
						<? if(!empty($subDomainName)): ?>
							<li>
								<?= $subDomainName ?>
									<ul>
						<? endif; ?>
						<? foreach($subDomainConfigs as $domain => $file): 
							$domainConfigClass = Foomo\Config::getDomainConfigClassName($domain);
							$displayThisConfig = $model->showConfigModule == $module && $model->showConfigDomain == $domain && $model->showConfigSubDomain == $subDomainName;
						?>
							<li>
								<?= $module . (!empty($subDomainName)?'/' . $subDomainName . '/':'/') . $domain ?>
								<ul>
									<li><a onclick="$('#<?= $editorId = str_replace('.', '-', $module . '-' . $domain . '-' . $subDomainName) ?>').toggle(300)">Edit</a></li>
									<li><?= $view->link('delete', 'deleteConf', array($module, $domain, $subDomainName), 'delete conf') ?></li>
								</ul>
								<div id="<?= $editorId ?>" style="display:<?= $displayThisConfig?'block':'none' ?>">
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
									<ul class="oldConfigs">
										<? foreach($oldOnes as $oldConfig): ?>
											<li>
												<?= date('Y-m-d H:i:s', $oldConfig->timestamp) ?>
												<?= $view->partial('old', array('oldConfig' => $oldConfig)) ?>
											</li>
										<? endforeach; ?>
									</ul>
								<? endif; ?>
							</li>
						<? endforeach; ?>
						<? if(!empty($subDomainName)): ?>
								</ul>
							</li>
						<? endif; ?>
					<? endforeach; ?>
					</ul>
				</li>
			<? endforeach; ?>
		</ul>
		<div id="configManagerOldConfigs">
			<? if(count($foundOldConfigs) < count($oldConfigs)): ?>
				Old configurations
				<ul class="oldConfigs">
					<? foreach($oldConfigs as $oldConfig): ?>
						<? if(!in_array($oldConfig, $foundOldConfigs)):
							$showOldConfId = $view->escape(str_replace('.' , '-', 'oldConf-' . $oldConfig->id ));
						?>
							<li>
								<?=	$oldConfig->module . '/' . (($oldConfig->domain != '')?$oldConfig->domain . '/':'') . $oldConfig->name ?> ( <?= date('Y-m-d H:i:s', $oldConfig->timestamp) ?> )
								<?= $view->partial('old', array('oldConfig' => $oldConfig)) ?>
							</li>
						<? endif; ?>
					<? endforeach; ?>
				</ul>
			<? endif; ?>
		</div>
	</div>
</div>
