<?
/* @var $view Foomo\MVC\View */
/* @var $model Foomo\Frontend\Model */
//var_export($model->navi);

$level = 0;

/**
 * @todo: make partial
 */
function renderLeaves($view, $leaf, $level) {
	
	$output = '<div class="level'.$level.'"><ul>' . PHP_EOL;
	
	foreach ($leaf['leaves'] as $subLeaf){
		
		if($subLeaf['active']){
			$output .= '<li class="selected">';
		} else {
			$output .= '<li class="default">';
		}
	
		if (!is_null($subLeaf['link'])) {
			$output .= '<a href="' . $view->url('showMVCApp', array($subLeaf['link']['app'], $subLeaf['link']['action'])) .'" target="' . $subLeaf['link']['target'] . '">'.$subLeaf['link']['name'].'</a>';
		} else {
			$output .= $subLeaf['name'] . PHP_EOL;
		}
		
		if (!empty ($subLeaf['leaves']) ) {
			$level++;
			$output .= renderLeaves($view, $subLeaf, $level);
		}
		$output .= '</li>' . PHP_EOL;
	}
	
	$output .= '</ul></div>' . PHP_EOL;
	
	return $output;
}

?>
<nav id="menuMain">
	<?= renderLeaves($view, $model->navi['Root'], $level); ?>
</nav>
<div id="breadcrumb"><a href="<?= \htmlspecialchars($view->url('default', array())) ?>">Home</a> / </div>
