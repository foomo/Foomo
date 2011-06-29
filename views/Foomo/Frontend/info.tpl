<?= $view->partial('header') ?>
<?= $view->partial('menu') ?>
<div id="main">
	<div id="fullContent">
		<ul>
			<li>
				Session
				<p>
					<? if(Foomo\Session::getEnabled()): ?>
						id   : <?= $view->escape(Foomo\Session::getSessionId()) ?><br>
						age  : <?= Foomo\Session::getAge() ?>
					<? else: ?>
						not enabled
					<? endif; ?>
				</p>
			</li>
			<li>
				Enviroment
				<?= $view->partial('foomoInfo') ?>
			</li>
			<li>
				foomo constants
				<ul>
				<? foreach(get_defined_constants() as $k => $v):
					if(substr($k, 0, 6) != 'Foomo\\') {
						continue;
					}
				?>
					<li><?= $view->escape($k) ?> : <?= $view->escape($v) ?></li>
				<? endforeach; ?>
				</ul>
			</li>
			<li>
				Classmap
				<ul>
				<?
					$moduleClassMap = array();
					foreach(\Foomo\Modules\Manager::getEnabledModules() as $enabledModuleName) {
						$moduleClassMap[$enabledModuleName] = array();
					}
					foreach(\Foomo\AutoLoader::getClassMap() as $className => $classFilename) {
						$moduleClassMap[\Foomo\Modules\Manager::getModuleByClassName($className)][] = $className;
					}
					foreach($moduleClassMap as $moduleName => $moduleClasses):

				?>
					<li>
						<?= $moduleName ?> (<?= count($moduleClasses) ?>)
						<ul>
						<? foreach($moduleClasses as $moduleClass): ?>
							<li><?= $moduleClass ?></li>
						<? endforeach; ?>
						</ul>
					</li>
					<? endforeach; ?>
				</ul>
			</li>
			<li>
				php
				<ul>
					<li><?= $view->link('All', 'info', array('php', '')); ?></li>
					<li><?= $view->link('Configuration', 'info', array('php', INFO_CONFIGURATION)); ?></li>
					<li><?= $view->link('Variables', 'info', array('php', INFO_VARIABLES)); ?></li>
					<li><?= $view->link('Modules', 'info', array('php', INFO_MODULES)); ?></li>
					<li><?= $view->link('Environment', 'info', array('php', INFO_ENVIRONMENT)); ?></li>
				</ul>
			</li>
			<? if(function_exists('apc_fetch')): ?>
			<li>
				<?= $view->link('APC', 'info', array('APC', '')); ?>
			</li>
			<? endif; ?>

			<li>
				<?= $view->link('Memcache', 'info', array('Memcache', '')); ?>
			</li>

		</ul>
	</div>
</div>
<?= $view->partial('footer') ?>
