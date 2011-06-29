<?php

/*
 * bestbytes-copyright-placeholder
 */

namespace Foomo;

/**
 * frontend for foomo
 */
class Frontend extends \Foomo\MVC\AbstractApp
{
	//---------------------------------------------------------------------------------------------
	// ~ Constants
	//---------------------------------------------------------------------------------------------

	const BASIC_AUTH_REALM = 'foomo-toolbox';

	//---------------------------------------------------------------------------------------------
	// ~ Public static methods
	//---------------------------------------------------------------------------------------------

	/**
	 * @param string $title
	 */
	public static function setUpToolbox($title='foomo toolbox')
	{
		if(!file_exists(BasicAuth::getDefaultAuthFilename())) {
			if(!headers_sent()) header('Content-Type: text/plain');
			die('default auth file does not exist - you might want to run setup.php');
		}

		BasicAuth::auth(self::BASIC_AUTH_REALM);

		$doc = HTMLDocument::getInstance()->setTitle($title);

		if(defined('Foomo\\ROOT_HTTP')) {
			$doc
				->addStylesheets(array(
                    'http://fonts.googleapis.com/css?family=Ubuntu:regular,bold&v1',
                    \Foomo\ROOT_HTTP . '/css/reset.css',
					\Foomo\ROOT_HTTP . '/css/app.css',
					\Foomo\ROOT_HTTP . '/css/module.css'
				))
				->addJavascripts(array(
					ROOT_HTTP . '/js/radJs.js',
					ROOT_HTTP . '/js/jquery-1.6.1.min.js',
					ROOT_HTTP . '/js/modules.js'
				))
			;
		}
	}
}
