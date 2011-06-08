<table title="environment">
	<thead>
		<tr>
			<td>Name</td>
			<td>Value</td>
			<td>Source</td>
			<td>Required</td>
		</tr>
	</thead>
	<tbody>
<?


$env = array(
	'Foomo\\SYSTEM_START_MICRO_TIME' => array(
		'docs' => '(micro)timestamp created at the first executed line',
		'required' => false
	),
	'Foomo\\ROOT' => array(
		'docs' => 'system root folder - typically the folder containing modules, var and config (that is in a default layout)',
		'required' => false
	),
	'FOOMO_RUN_MODE' => array(
		'docs' => 'run mode test, development or production',
		'required' => true
	),
	'Foomo\\ROOT_HTTP' => array(
		'docs' => 'that is where',
		'required' => false
	),
	'Foomo\\CORE_CONFIG_DIR_MODULES' => array(
		'docs' => 'modules root folder usually \Foomo\ROOT/modules can be overwritten with $_SERVER[\'\Foomo\CORE_CONFIG_DIR_MODULES\']',
		'required' => false
	),
	'Foomo\\CORE_CONFIG_DIR_VAR' => array(
		'docs' => 'var root folder usually \Foomo\ROOT/modules can be overwritten in \Foomo\ROOT/foomoCoreSettings.inc.php',
		'required' => false
	),
	'Foomo\\CORE_CONFIG_DIR_CONFIG' => array(
		'docs' => 'config root folder usually \Foomo\ROOT/modules can be overwritten in \Foomo\ROOT/foomoCoreSettings.inc.php',
		'required' => false
	),
	'FOOMO_CACHE_QUERYABLE' => array(
		'docs' => 'config for queryable cache persistor',
		'required' => true
	),
	'FOOMO_CACHE_FAST' => array(
		'docs' => 'config for fast cache persistor',
		'required' => true
	)
);

foreach($env as $name => $info):
	if(isset($_SERVER[$name])) {
		$value = $_SERVER[$name];
		$source = '$_SERVER';
	} else if(defined($name)) {
		$value = constant($name);
		$source = 'constant';
	} else {
		$value = null;
		$source = 'none';
	}
?>
	<tr class="<?= (is_null($value) && $info['required'])?'notOk':'ok' ?>">
		<td><?= $name ?></td>
		<td><?= htmlspecialchars($value) ?></td>
		<td><?= htmlspecialchars($source) ?></td>
		<td><?= ($info['required']?'yes':'no') ?></td>
	</tr>
	<? if(isset($env[$name])): ?>
		<tr>
			<td class="docs" colspan="4"><?= $info['docs'] ?></td>
		</tr>
	<? endif; ?>
<? endforeach; ?>
	</tbody>
</table>