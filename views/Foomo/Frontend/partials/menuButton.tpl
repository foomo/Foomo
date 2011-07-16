<?
	if(empty($parameters)){
		$parameters = array();
	}
	
	$methodMatch = $view->currentAction == $url || $view->currentAction == 'action' . ucfirst($url);
	$methodMatch = $methodMatch  && $parameters == $view->currentParameters;
	
	$selected = '';
	if ($methodMatch) {
		$selected = 'class="selected"';
	}
	
	
?>
<div class="menuSubButton">
	<a href="<?= $view->escape($view->url($url,$parameters)) ?>">
		<div id="buttonLeft" <?= $selected ?>></div>
		<div id="buttonMiddle" <?= $selected ?>><?= $name ?></div>
		<div id="buttonRight" <?= $selected ?>></div>
	</a>
</div>