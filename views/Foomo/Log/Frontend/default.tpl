<?php
/* @var $model Foomo\Log\Frontend\Model */
/* @var $view  Foomo\MVC\View */
$doc = Foomo\HTMLDocument::getInstance();
$doc->addOnLoad('document.getElementById(\'filterInput\').value = \'\';');
?>
<div id="fullContent">
	<p>Webtail - Compose filter functions and tail the server</p>
	<ul>
	<? foreach($model->getFiltersProviders() as $module => $providers): ?>
		<li>
			<?= $module ?>
			<ul>
				<? foreach($providers as $providerName => $provider): ?>
				<li>
					<?= $providerName ?>
					<ul>
						<? foreach($provider as $filterName => $docComment): ?>
						<li title="<?= $docComment?$view->escape(str_replace(array('/**', '*/', ' * ', '  ', PHP_EOL), '',$docComment)):'' ?>">
							<a
								href="#"
								onclick="
									document.getElementById('filtersDisplay').innerHTML = document.getElementById('filterInput').value = document.getElementById('filterInput').value + '<?= $view->escape(addslashes($providerName . '::' . $filterName)) ?>' + String.fromCharCode(10)"
							><?= $filterName ?></a>
						</li>
						<? endforeach; ?>
					</ul>
				</li>
				<? endforeach ?>
			</ul>
		</li>
	<? endforeach; ?>
	</ul>
	<p>Selected filters</p>
	<pre id="filtersDisplay"></pre>
	<form action="<?= $view->escape($view->url('webTail'))?>" method="post">
		<input type="hidden" name="filters" id="filterInput" value="">
		<input type="submit" value="tail">
	</form>
</div>
