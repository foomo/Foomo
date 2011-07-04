<?php
/* @var $model Foomo\Log\Frontend\Model */
/* @var $view  Foomo\MVC\View */
$doc = Foomo\HTMLDocument::getInstance();
$doc->addOnLoad('document.getElementById(\'filterInput\').value = \'\';');
?>
<div id="fullContent">
	<h2>Webtail - Compose filter functions and tail the server</h2>
	
	<form action="<?= $view->escape($view->url('webTail'))?>" method="post">
		<div class="greyBox">
			<br>
			<ul>
			<? foreach(\Foomo\Log\Utils::getFilterProviders() as $module => $providers): ?>
				<li>
					<?= $module ?>
					<ul>
						<? foreach($providers as $providerName => $provider): ?>
						<li>
							<?= $providerName ?>
							<ul>
								<? foreach($provider as $filterName => $docComment): ?>
								<li title="<?= $docComment?$view->escape(str_replace(array('/**', '*/', ' * ', '  ', PHP_EOL), '',$docComment)):'' ?>" style="float: left; margin-right: 20px;">
									<input type="checkbox" class="checkBox" name="filterInput" value="<?= $view->escape(addslashes($providerName . '::' . $filterName)) ?>">
									<!-- onclick="document.getElementById('filterInput').value = document.getElementById('filterInput').value + '<?= $view->escape(addslashes($providerName . '::' . $filterName)) ?>' + String.fromCharCode(10)"> -->
									
									<?= $filterName ?>
								</li>
								<? endforeach; ?>
							</ul>
						</li>
						<? endforeach ?>
					</ul>
				</li>
			<? endforeach; ?>
			</ul>
			<br>


			<!--<input type="hidden" name="filters" id="filterInput" value="">-->
			<div class="formBox">
				<input class="submitButton" type="submit" value="Open Tail"/>
			</div>

		</div>
	</form>
</div>
