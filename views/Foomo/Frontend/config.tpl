<?
/*
namespace Foomo\MVC;

$view->addResources(array(
	new View\Resource('text/javacript', ''),
	new View\Resource\CSS(''),
	View\Resource::css('')
));
*/		
?>
<?= $view->partial('header') ?>
<?= $view->partial('menu') ?>
<?= $view->partial('app', array('appName' => 'Foomo\\Config\\Frontend')) ?>
<?= $view->partial('footer') ?>
