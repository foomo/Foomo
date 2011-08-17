<?
/* @var $view Foomo\MVC\View */
/* @var $model Foomo\Frontend\Model */

$level = 0;

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
			$output .= '<li class="'.$classes.'"><a href="' . \htmlspecialchars($view->url('showMVCApp', array_merge(array($subLeaf['link']['app'], $subLeaf['link']['action']), $subLeaf['link']['parameters']))) .'" target="' . $subLeaf['link']['target'] . '">'.$subLeaf['link']['name'].'</a>'. PHP_EOL;

		} else {
			if ($level > 0) $classes .= ' down';
			$output .= '<li class="'.$classes.'"><span>'.$subLeaf['name'].'</span>' . PHP_EOL;
		}

		if (!empty ($subLeaf['leaves']) ) {
			$output .= renderLeaves($view, $subLeaf, $level + 1);
		}
		$output .= '</li>' . PHP_EOL;
	}

	$output .= '</ul></div>' . PHP_EOL;

	return $output;
}


function renderBreadcrumb($view, $leaf) {

	$output = '';

	foreach ($leaf['leaves'] as $subLeaf){

		if($subLeaf['active']){ //  && !is_null($subLeaf['link'])
			if (!is_null($subLeaf['link'])) {
				$output .= ' / <a href="'. \htmlspecialchars($view->url('showMVCApp', array_merge(array($subLeaf['link']['app'], $subLeaf['link']['action']), $subLeaf['link']['parameters']))) .'">'.$subLeaf['link']['name'].'</a>';
			} else {
				$output .= ' / '.$subLeaf['name'];
			}
		}

		if (!empty ($subLeaf['leaves']) ) {
			$output .= renderBreadcrumb($view, $subLeaf);
		}

	}

	return $output;
}

?>
<nav id="menuMain">
	<?= renderLeaves($view, $model->navi['Root'], $level); ?>
</nav>
<div id="breadcrumb">
	<a href="<?= \htmlspecialchars($view->url('default', array())) ?>">Home</a><?= renderBreadcrumb($view, $model->navi['Root']); ?>
</div>
