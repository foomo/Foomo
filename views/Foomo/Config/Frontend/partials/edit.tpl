<?php

/* @var $model Foomo\Config\Frontend\Model */

$domainConfigClass = Foomo\Config::getDomainConfigClassName($model->currentConfigDomain);
$def = new $domainConfigClass;
$config = Foomo\Config::getCurrentConfYAML($model->currentConfigModule, $model->currentConfigDomain, $model->currentConfigSubDomain);
$delParms = array(
	'module' => $model->currentConfigModule,
	'domain' => $model->currentConfigDomain,
	'subDomain' => $model->currentConfigSubDomain
);
?>
<?= $view->partial('confMenu') ?>
<table id="yamlEditTable">
	<tr class="yamlEdit">
		<td>
			<form action="<?= $view->url('actionSetConf'); ?>" method="post">
				<input type="hidden" name="module" value="<?=  $model->currentConfigModule; ?>">
				<input type="hidden" name="domain" value="<?=  $model->currentConfigDomain; ?>">
				<input type="hidden" name="subDomain" value="<?=  $model->currentConfigSubDomain; ?>">
				<p>current</p>
				<textarea
					rows="<?= count(explode(PHP_EOL, $config)); ?>"
					class="yamlEdit"
					name="yaml"
				><?=  $config ?></textarea>
				<p>
					<input type="submit" value="update">
				</p>
			</form>
		</td>
		<td>
			<form action="<?= $view->url('actionRestoreDefault'); ?>" method="post">
				<input type="hidden" name="module" value="<?=  $model->currentConfigModule; ?>">
				<input type="hidden" name="domain" value="<?=  $model->currentConfigDomain; ?>">
				<input type="hidden" name="subDomain" value="<?=  $model->currentConfigSubDomain; ?>">
				<p title="you can not change the default - this textarea is only enabled, because you can not copy text from a disabled text field in some browsers ...">default</p>
				<textarea
					title="you can not change the default - this textarea is only enabled, because you can not copy text from a disabled text field in some browsers ..."
					rows="<?= count(explode(PHP_EOL, $config)); ?>"
					class="yamlEdit"
				><?=  \Foomo\Yaml::dump($def->getDefault()) ?></textarea>
				<p>
					<input type="submit" value="restore default">
				</p>
			</form>
		</td>
	</tr>
</table>
