<?php

/*
 * bestbytes-copyright-placeholder
 */

namespace Foomo\Modules;

use Foomo\MVC\AbstractApp;

/**
 * manage modules
 */
class Frontend extends AbstractApp
{
	//---------------------------------------------------------------------------------------------
	// ~ Constants
	//---------------------------------------------------------------------------------------------

	public function __construct()
	{
		$HTMLDoc = \Foomo\HTMLDocument::getInstance();
		$HTMLDoc->addJavascripts(array(\Foomo\ROOT_HTTP . '/js/jquery-1.6.1.min.js'));
		$HTMLDoc->addJavascripts(array(\Foomo\ROOT_HTTP . '/js/modules.js'));
		parent::__construct(get_class($this));
	}
}