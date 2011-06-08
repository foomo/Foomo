<? 
$printer = new \Foomo\Log\Printer;

?>
    error    : <?= $model['str'] ?>

    type     : <?= $printer->phpErrorIntToString($model['no']) ?>

    time     : <?= $model['time'] ?> (<?= date('Y-m-d H:i:s', $model['time']) ?>)
    templ    : 
<? 
foreach(array_reverse($model['templStack']) as $templateFile): 
	if(strpos($templateFile, \Foomo\CORE_CONFIG_DIR_MODULES) === 0) {
		$templateFile = substr($templateFile, strlen(\Foomo\CORE_CONFIG_DIR_MODULES) + 1);
	}
?>
               <?= $templateFile ?>

<? endforeach; ?>
<? if(isset($model['file'])): ?>
    file     : <?= $model['file'] ?>

    line     : <?= $model['line'] ?>

<? endif; ?>
<? if(isset($model['class'])): ?>

    method   : <?= $model['class'] . $model['type'] . $model['function'] ?>(<?= implode(', ', $model['args']) ?>)
<? elseif(isset($model['function'])): ?>


    function : <?= $model['function'] ?>(<?= implode(', ', $model['args']) ?>)
<? endif; ?>
<? if(count($model['trace'])>0): ?>
    stack    :
<? $i = 0;
    foreach($model['trace'] as $traceEntry):
?>
        <?= str_pad(($i ++) . ' ', 40, '-') ?>

<? if(isset($traceEntry['file'])): ?>
            file     : <?= $traceEntry['file'] ?>

            line     : <?= $traceEntry['line'] ?>

<? endif; ?>
<? if(isset($traceEntry['class'])): ?>
            method   : <?= $traceEntry['class'] . $traceEntry['type'] . $traceEntry['function'] ?>(<?= implode(', ', $traceEntry['args']) ?>)
<? elseif(isset($traceEntry['function'])): ?>
            function : <?= $traceEntry['function'] ?>(<?= implode(', ', $traceEntry['args']) ?>)
<? endif; ?>
<? endforeach; ?>
<? endif; ?>
    --------------------