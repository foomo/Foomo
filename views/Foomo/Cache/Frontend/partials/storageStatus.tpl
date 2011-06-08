<?php
/* @var $view Foomo\MVC\View */
/* @var $model Foomo\Cache\Frontend\Model */

$existence = $model->getStorageExists($resourceName);
$validity = $model->getValidationStatus($resourceName);

?>
<div class="line">
	<? if($existence === true && $validity === true): ?>
		<div class="ok">
			Storage structure for resource exists and is valid: no action required.
		</div>
	<? elseif ($existence === true && $validity === false): ?>
		<div class="invalid">
			Storage structure exists, but is INVALID! Validate to review inconsistencies between structure and annotation. Setup structure if required!
		</div>
	<? else: ?>
		<div class="error">
			Storage structure DOES NOT exists. Setup storage. Alternatively it will be setup automatically when first required!
		</div>
	<? endif ?>
</div>