<? 
$resources = Foomo\Modules\Manager::getModuleResources($moduleName);
if(count($resources) == 0) { return; }
?>
<ul>
<?
$allValid = true;
foreach($resources as $modResource) {
	/* @var $modResource Foomo\Modules\Resource */
	if($modResource->resourceValid()) {
		$modResClass = 'valid';
	} else {
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
