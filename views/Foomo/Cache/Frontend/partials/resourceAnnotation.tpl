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

Parsed data<br>
<b><?= $view->partial('annotation', (array) $resourceRefl->description) ?></b><br>
<br>
Derived from<br>
<pre>
<?
/**
 * orginal doc comment @jan todo
 */
?>
</pre>
<? 

?>

<? if(count ($unknown) > 0): ?>
<div class="errorMessage">
		The following unsupported parameter(s) were found and ignored in the resource annotation: 
		<br><b><?= \implode(', ', $unknown)?></b><br>
		<b><?= $view->escape($model->getAnnotationValidationStatus($model->currentResourceName))?></b>
</div>
<? endif; ?>