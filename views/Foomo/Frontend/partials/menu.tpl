<?
/* @var $view Foomo\MVC\View */
/* @var $model Foomo\Frontend\Model */



$naviArray = array(	array('name' => 'Configuration', 'url' => 'config'),
					array('name' => 'Modules', 'url' => 'modules'),
					array('name' => 'Log', 'url' => 'log'),
					array('name' => 'Info', 'url' => 'info'),
					array('name' => 'Auth', 'url' => 'basicAuth'),
					array('name' => 'Cache', 'url' => 'cache')
					);

?>
<nav id="menuMain">
	<ul>
		<?
		foreach($naviArray as $naviItem){
			$methodMatch = $view->currentAction == $naviItem['url'] || $view->currentAction == 'action' . ucfirst($naviItem['url']);
			if(!$methodMatch){
				echo '<li class="default"><a href="'. \htmlspecialchars($view->url($naviItem['url'], array())) .'" target="_self">'.$naviItem['name'].'</a></li>';
			} else {
				echo '<li class="default selected"><a href="'. \htmlspecialchars($view->url($naviItem['url'], array())) .'" target="_self">'.$naviItem['name'].'</a></li>';
			}
		}
		?>
	</ul>
</nav>
<div id="breadcrumb"><a href="<?= \htmlspecialchars($view->url('default', array())) ?>">Home</a> / </div>
