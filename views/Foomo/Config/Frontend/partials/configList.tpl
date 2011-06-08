<?
/* @var $view \Foomo\MVC\View */
$configs = Foomo\Config\Utils::getConfigs();
$oldConfigs = Foomo\Config\Utils::getOldConfigs();
$foundOldConfigs = array();
?>
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
					<? foreach($subDomainConfigs as $domain => $file): ?>
						<li>
							<?= $view->link($domain, 'showConf', array($module, $domain, $subDomainName)) ?>
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
											<?= 
												date('Y-m-d H:i:s', $oldConfig->timestamp) . '<br>' . 
												$view->link('show', 'showOldConf', array($oldConfig->id), 'take a look at an old config') . ' | '.
												$view->link('delete', 'deleteOldConf', array($oldConfig->id), 'delete') . ' | ' .
												$view->link('restore', 'restoreOldConf', array($oldConfig->id), 'restore')
											?>
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
					<? if(!in_array($oldConfig, $foundOldConfigs)):?>
						<li>

							<?=
								$oldConfig->module . '/' . (($oldConfig->domain != '')?$oldConfig->domain . '/':'') . $oldConfig->name . '<br>' . 
								date('Y-m-d H:i:s', $oldConfig->timestamp) . '<br>' . 
								$view->link('show', 'showOldConf', array($oldConfig->id), 'take a look at an old config') . ' | '.
								$view->link('delete', 'deleteOldConf', array($oldConfig->id), 'delete') . ' | ' .
								$view->link('restore', 'restoreOldConf', array($oldConfig->id), 'restore')
							?>
						</li>
					<? endif; ?>
				<? endforeach; ?>
			</ul>
		<? endif; ?>
	</div>
</div>

