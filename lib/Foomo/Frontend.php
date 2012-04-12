<?php

/*
 * This file is part of the foomo Opensource Framework.
 *
 * The foomo Opensource Framework is free software: you can redistribute it
 * and/or modify it under the terms of the GNU Lesser General Public License as
 * published  by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * The foomo Opensource Framework is distributed in the hope that it will
 * be useful, but WITHOUT ANY WARRANTY; without even the implied warranty
 * of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License along with
 * the foomo Opensource Framework. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Foomo;

/**
 * frontend for foomo
 * 
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
class Frontend extends \Foomo\MVC\AbstractApp
{
	const NAME = 'Foomo';
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

		$doc = \Foomo\HTMLDocument::getInstance()->setTitle($title);

		if(defined('Foomo\\ROOT_HTTP')) {
			$doc
				->addStylesheets(array(
                    (isset($_SERVER['HTTPS'])?'https':'http') . '://fonts.googleapis.com/css?family=Ubuntu:regular,bold&v1',
                    \Foomo\ROOT_HTTP . '/css/reset.css',
					\Foomo\ROOT_HTTP . '/css/app.css',
					\Foomo\ROOT_HTTP . '/css/module.css'
				))
				->addJavascripts(array(
					\Foomo\ROOT_HTTP . '/js/jquery-1.6.1.min.js',
					\Foomo\ROOT_HTTP . '/js/modules.js'
				))
			;
		}
	}
}
