<? 
$resources = Foomo\Modules\Manager::getModuleResources($moduleName);
if(count($resources) == 0) { return; }
?>
<ul>
<?
$allValid = true;
foreach($resources as $k => $modResource) {
	/* @var $modResource Foomo\Modules\Resource */
	if(is_object($modResource) && $modResource->resourceValid()) {
		$modResClass = 'valid';
	} else {
		if(!is_object($modResource)) {
			var_dump($k, $modResource);
			continue;
		}
		$allValid = false;
		$modResClass = 'invalid';
	}
	echo '<li><pre class="'.$modResClass.'">'.htmlspecialchars($modResource->resourceStatus()).'</pre></li>';
}
?>
</ul>
<? if(!$allValid):?>
	<?= $view->link('try create missing resoures for ' . $moduleName, 'actionTryCreateModuleResources', array($moduleName)) ?>
<? endif; ?>
