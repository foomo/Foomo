<?php
extract($model); 
?><h3>result of action <?php echo $action; ?></h3>
<pre>
<?php 
if(is_string($result)) {
	echo $result;
} else {
	echo htmlspecialchars(var_export($result, true));
}
?>
</pre>