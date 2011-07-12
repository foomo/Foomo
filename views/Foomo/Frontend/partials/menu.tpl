<?
/* @var $view Foomo\MVC\View */
/* @var $model Foomo\Frontend\Model */
//var_export($model->navi);

$level = 0;

/**
 * @todo: make partial
 * @param Foomo\MVC\View $view
 */
function renderLeaves($view, $leaf, $level) {

	$output = '<div class="level'.$level.'"><ul>' . PHP_EOL;

	foreach ($leaf['leaves'] as $subLeaf){

		$classes = '';

		if($subLeaf['active']){
			$classes .= 'selected';
		} else {
			$classes .= 'default';
		}

		if (!is_null($subLeaf['link'])) {
			if ($level > 0 && !empty ($subLeaf['leaves'])) $classes .= ' down';
			$output .= '<li class="'.$classes.'"><a href="' . $view->url('showMVCApp', array_merge(array($subLeaf['link']['app'], $subLeaf['link']['action']), $subLeaf['link']['parameters'])) .'" target="' . $subLeaf['link']['target'] . '">'.$subLeaf['link']['name'].'</a>';
		} else {
			if ($level > 0) $classes .= ' down';
			$output .= '<li class="'.$classes.'">'.$subLeaf['name'] . PHP_EOL;
		}

		if (!empty ($subLeaf['leaves']) ) {
			$output .= renderLeaves($view, $subLeaf, $level + 1);
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
