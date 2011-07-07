<?php
/* @var $model Foomo\Log\Frontend\Model */
/* @var $view  Foomo\MVC\View */
$doc = Foomo\HTMLDocument::getInstance();
$doc->addOnLoad('document.getElementById(\'filterInput\').value = \'\';');
?>
<script language="JavaScript" type="text/javascript">
// <![CDATA[ <!--

	var itemArray = [];

	function addItem(which){
		
		var found = false;
		
		for (var i = 0; i < itemArray.length; i++){
			if(itemArray[i].replace(/\\/g,'') == which.replace(/\\/g,'')){
				found = true;
			}
		}
		
		if(!found){
			itemArray.push(which);
		}
		
		buildItems();

	}

	function deleteItem(which){

		var newArray = [];

		for (var i = 0; i < itemArray.length; i++){
			//alert(itemArray[i].replace(/\\/g,'')+" == "+String(which));
			if(itemArray[i].replace(/\\/g,'') != which.replace(/\\/g,'')){
				newArray.push(itemArray[i]);
			}

		}
		
		itemArray = newArray;
		buildItems();

	}



	function buildItems(){

		var inputVal = '';
		var selectedVal = '';

		for (var i = 0; i < itemArray.length; i++){
			inputVal += itemArray[i]+ String.fromCharCode(10);
			selectedVal += '<div style="cursor: pointer; width: auto; margin: 5px 0;" class="linkButtonSmallYellow" onclick="deleteItem(\''+itemArray[i]+'\')"><span style="font-size:19px;">-</span> '+itemArray[i]+'</div>';
		}

		$('#filterInput').val(inputVal);
		$('#selectedFilter').html(selectedVal);
		
	}
	

		
	
// --> ]]>
</script>

<div id="fullContent">
	<h2>Webtail - Compose filter functions and tail the server</h2>
	
	<form action="<?= $view->escape($view->url('webTail'))?>" method="post">
		<div class="greyBox">
			
			<div class="formBox">
				<div class="whiteBox">
					<div class="innerBox">
						<b>Filters</b>
						<ul>
						<? foreach(\Foomo\Log\Utils::getFilterProviders() as $module => $providers): ?>
							<li style="padding:0;">
								<?= $module ?>
								<ul>
									<? foreach($providers as $providerName => $provider): ?>
									<li>
										<?= $providerName ?>
										<ul>
											<? foreach($provider as $filterName => $docComment): ?>
											<li style="float: left; margin-right: 20px; padding-left:0;">
												<div class="linkButtonSmallYellow" title="<?= $docComment?$view->escape(str_replace(array('/**', '*/', ' * ', '  ', PHP_EOL), '',$docComment)):'' ?>"
												style="cursor: pointer;"
												onclick="addItem('<?= $view->escape(addslashes($providerName . '::' . $filterName)) ?>')"
												><span style="font-size:15px;">+</span> <?= $filterName ?></div>
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
					</div>
				</div>
			</div>
			
			
			<div class="formBox">
				<div class="formTitle">Selected Filters</div>
				<div id="selectedFilter">
					
				</div>
			</div>

			<div class="formBox">
				<input type="hidden" name="filters" id="filterInput" value="">
				<input class="submitButton" type="submit" value="Open Tail"/>
			</div>

		</div>
	</form>
</div>
