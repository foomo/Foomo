<?php

/*
 * bestbytes-copyright-placeholder
 */

namespace Foomo;

use Foomo\MVC\AbstractApp;

/**
 * frontend for foomo
 */
class Frontend extends AbstractApp
{
	//---------------------------------------------------------------------------------------------
	// ~ Constants
	//---------------------------------------------------------------------------------------------

	const NAME = 'Foomo.toolbox';

	//---------------------------------------------------------------------------------------------
	// ~ Public static methods
	//---------------------------------------------------------------------------------------------

	public static function setUpToolbox($title = 'foomo toolbox')
	{
		if(!file_exists(BasicAuth::getDefaultAuthFilename())) {
			if(!headers_sent()) header('Content-Type: text/plain');
			die('default auth file does not exist - you might want to run setup.php');
		}

		BasicAuth::auth('foomo-toolbox');

		$doc = HTMLDocument::getInstance()->setTitle($title);

		if(defined('Foomo\\ROOT_HTTP')) {
			$doc
				->addStylesheets(array(
                    'http://fonts.googleapis.com/css?family=Ubuntu:regular,bold&v1',
                    \Foomo\ROOT_HTTP . '/css/reset.css',
					\Foomo\ROOT_HTTP . '/css/module.css',
					\Foomo\ROOT_HTTP . '/css/app.css',
				))
				->addJavascripts(array(
					ROOT_HTTP . '/js/radJs.js',
					ROOT_HTTP . '/js/jquery-1.6.1.min.js'
				))
			;
		}
	}
}
