<?php
/* @var $view Foomo\MVC\View */
/* @var $model Foomo\Cache\Frontend\Model */
/* @var $resource Cache */

/* @var $defaultAnotation Foomo\Cache\CacheResourceDescription */
/* @var $userDataHash array */

$resourceRefl = $model->getResourceRefl($model->currentResourceName);

$depStr = implode(', ', $resourceRefl->description->dependencies);

$userDataHash = $model->getRawAnnotationData($model->currentResourceName);
$userSetProps = array_keys($userDataHash);

$defaultAnotation = new \Foomo\Cache\CacheResourceDescription;
$unknown = array();
$known = array('lifeTime','lifeTimeFast', 'dependencies', 'invalidationPolicy');
//var_dump($userDataHash, $model->getAnnotationValidationStatus($model->currentResourceName), $resourceRefl);//, $userSetProps, $model->currentResourceName);
foreach($userSetProps as $param) {
	if (!\in_array($param, $known)) $unknown[] = $param;
}
?>

<p>Parsed data</p>
<?= $view->partial('annotation', (array) $resourceRefl->description) ?>

<p>Derived from</p>
<pre>
/**
 * orginal doc comment @jan todo
 */
</pre>
<? 

?>

<div id="errorMessage">
	<? if(count ($unknown) > 0): ?>
		The following unsupported parameter(s) were found and ignored in the resource annotation: 
		<p><?= \implode(', ', $unknown)?></p>
	<? endif; ?>
	<div>
		<?= $view->escape($model->getAnnotationValidationStatus($model->currentResourceName))?>
	</div>
</div>