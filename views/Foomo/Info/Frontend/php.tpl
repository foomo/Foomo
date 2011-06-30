<?
/* @var $model Foomo\Info\Frontend\Model */
/* @var $view \Foomo\MVC\View */
?>

<style type="text/css">
#phpinfo {}
#phpinfo pre {}
#phpinfo a:link {}
#phpinfo a:hover {}
#phpinfo table {}
#phpinfo .center {}
#phpinfo .center table {}
#phpinfo .center th {}
#phpinfo td, th {}
#phpinfo h1 {}
#phpinfo h2 {}
#phpinfo .p {}
#phpinfo .e {}
#phpinfo .h {}
#phpinfo .v {}
#phpinfo .vr {}
#phpinfo img {}
#phpinfo hr {}
</style>


<div id="phpinfo" class="tabBox">
	<div class="tabNavi">
		<ul>
			<li class="selected">All</li>
			<li>Configuration</li>
			<li>Variables</li>
			<li>Modules</li>
			<li>Environment</li>
		</ul>
		<hr class="greyLine">
	</div>
	<div class="tabContentBox">

		<div class="tabContent tabContent-1 selected">
			<?= $model->getPhpInfo() ?>
		</div>

		<div class="tabContent tabContent-2">
			<?= $model->getPhpInfo(INFO_CONFIGURATION) ?>
		</div>

		<div class="tabContent tabContent-3">
			<?= $model->getPhpInfo(INFO_VARIABLES) ?>
		</div>

		<div class="tabContent tabContent-4">
			<?= $model->getPhpInfo(INFO_MODULES) ?>
		</div>

		<div class="tabContent tabContent-4">
			<?= $model->getPhpInfo(INFO_ENVIRONMENT) ?>
		</div>

	</div>
</div>