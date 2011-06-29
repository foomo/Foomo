<?
	if(!empty($selected)){
		$selected = 'class="selected"';
	} else {
		$methodMatch = $view->currentAction == $url || $view->currentAction == 'action' . ucfirst($url);
		$selected = '';
		if ($methodMatch) {
			$selected = 'class="selected"';
		}
	}

	if(empty($parameters)){
		$parameters = array();
	}

	if(empty($js)){
		$js = 'href="'. \htmlspecialchars($view->url($url,$parameters)) .'"';
	} else {

		#trigger_error("-------> ".$js);
	}
?>
<div class="buttonYellow">
	<a <?= $js ?> >

		<div id="buttonLeft" <?= $selected ?>></div>
		<div id="buttonMiddle" <?= $selected ?>><?= $name ?></div>
		<div id="buttonRight" <?= $selected ?>></div>
	</a>
</div>