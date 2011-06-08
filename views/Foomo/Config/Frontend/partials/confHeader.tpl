<?

try {
	$className = Foomo\Config::getDomainConfigClassName($name);
} catch(InvalidArgumentException $e) {
	$className = 'unknown config class';
}
?>
<p>
	<?=
		$className . '<br>' . $module . ($domain?'/' . $domain:'') . '/' . $name;
	?>
</p>
