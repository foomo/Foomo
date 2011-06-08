<?php

/*
 * bestbytes-copyright-placeholder
 */

namespace Foomo\Modules;

use Foomo\MVC\AbstractApp;

/**
 * manage modules
 */
class Frontend extends AbstractApp {
	public function __construct() 
	{
		$HTMLDoc = \Foomo\HTMLDocument::getInstance();
		$HTMLDoc->addStylesheets(array(\Foomo\ROOT_HTTP . '/css/apps/moduleManager.css'));
		$HTMLDoc->addJavascripts(array(\Foomo\ROOT_HTTP . '/js/jquery-1.6.1.min.js'));
		$HTMLDoc->addJavascripts(array(\Foomo\ROOT_HTTP . '/js/modules.js'));
		parent::__construct(get_class($this));
	}
}