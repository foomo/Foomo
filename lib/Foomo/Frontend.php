<?php

/*
 * bestbytes-copyright-placeholder
 */

namespace Foomo;

use Foomo\MVC\AbstractApp;

/**
 * frontend for foomo
 */
class Frontend extends AbstractApp {
	const NAME = 'Foomo.toolbox';
	public static function setUpToolbox($title = 'foomo toolbox')
	{
		if(!file_exists(BasicAuth::getDefaultAuthFilename())) {
			header('Content-Type: text/plain');
			die('default auth file does not exist - you might want to run setup.php');
		}

		BasicAuth::auth('foomo-toolbox');

		$doc = HTMLDocument::getInstance()->setTitle($title);
		
		if(defined('Foomo\\ROOT_HTTP')) {
			$doc
				->addStylesheets(array(
					\Foomo\ROOT_HTTP . '/css/module.css',
					\Foomo\ROOT_HTTP . '/css/app.css',
				))
				->addJavascripts(array(\Foomo\ROOT_HTTP . '/js/radJs.js'))
			;
		}
	}
}
