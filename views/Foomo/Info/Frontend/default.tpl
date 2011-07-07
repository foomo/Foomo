<?
/* @var $model Foomo\Config\Frontend\Model */
/* @var $view \Foomo\MVC\View */
?>
<div id="appContent">
	<h2>System Info</h2>

	<div class="toggleBox">
		<div class="toogleButton">
			<div class="toggleOpenIcon">+</div>
			<div class="toggleOpenContent">Session</div>
		</div>
		<div class="toggleContent">
			<pre>
<? if(Foomo\Session::getEnabled()): ?>
id   : <?= $view->escape(Foomo\Session::getSessionId()) . PHP_EOL ?>
age  : <?= Foomo\Session::getAge() ?> calls
<? else: ?>
Session is not enabled!
<? endif; ?></pre>
		</div>
	</div>

	<div class="toggleBox">
		<div class="toogleButton">
			<div class="toggleOpenIcon">+</div>
			<div class="toggleOpenContent">Enviroment</div>
		</div>
		<div class="toggleContent">
			<?= $view->partial('foomoInfo') ?>
		</div>
	</div>


	<div class="toggleBox">
		<div class="toogleButton">
			<div class="toggleOpenIcon">+</div>
			<div class="toggleOpenContent">Constants</div>
		</div>
		<div class="toggleContent">
			<table>
				<thead>
					<tr>
						<th>Name</th>
						<th>Value</th>
					</tr>
				</thead>
				<tbody>
					<? foreach(get_defined_constants() as $k => $v): ?>
					<? if(substr($k, 0, 6) != 'Foomo\\') continue; ?>
						<tr>
							<td><?= $view->escape($k) ?></td>
							<td><?= $view->escape($v) ?></td>
						</tr>
					<? endforeach; ?>
				</tbody>
			</table>
		</div>
	</div>

	<div class="toggleBox">
		<div class="toogleButton">
			<div class="toggleOpenIcon">+</div>
			<div class="toggleOpenContent">Classmap</div>
		</div>
		<div class="toggleContent">


			<?
				$moduleClassMap = array();
				foreach(\Foomo\Modules\Manager::getEnabledModules() as $enabledModuleName) {
					$moduleClassMap[$enabledModuleName] = array();
				}
				foreach(\Foomo\AutoLoader::getClassMap() as $className => $classFilename) {
					$moduleClassMap[\Foomo\Modules\Manager::getModuleByClassName($className)][] = $className;
				}
			?>
			<? foreach($moduleClassMap as $moduleName => $moduleClasses): ?>

				<div class="toggleBox">
					<div class="toogleButton">
						<div class="toggleOpenIcon">+</div>
						<div class="toggleOpenContent"><?= $moduleName ?> (<?= count($moduleClasses) ?>)</div>
					</div>
					<div class="toggleContent">
						<ul>
						<? foreach($moduleClasses as $moduleClass): ?>
							<li><?= $moduleClass ?></li>
						<? endforeach; ?>
						</ul>
					</div>
				</div>

			<? endforeach; ?>
		</div>
	</div>
</div>
