<?php

// in an MVC CONTEXT you will usually use the MVCView built in translation,
// which of course is also just a Translation

/* @var $view Foomo\MVC\View */

// if you want to override the default locale chain

$view->setLocaleChain(array('de'));

?>

<h1><?= $view->_('TRANSLATION_IN_A_VIEW')?></h1>
