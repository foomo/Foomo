<?
	$methodMatch = $view->currentAction == $url || $view->currentAction == 'action' . ucfirst($url);
	$selected = '';
	if ($methodMatch) {
		$selected = 'class="selected"';
	}
	
	if(empty($parameters)){
		$parameters = array();
	}
	
?>
<div class="menuSubButton">
	<a href="<?= \htmlspecialchars($view->url($url,$parameters)) ?>">
		<div id="buttonLeft" <?= $selected ?>></div>
		<div id="buttonMiddle" <?= $selected ?>><?= $name ?></div>
		<div id="buttonRight" <?= $selected ?>></div>
	</a>
</div>