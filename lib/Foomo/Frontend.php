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
	public static function auth($authDomain = BasicAuth::DEFAULT_AUTH_DOMAIN, $realm = self::BASIC_AUTH_REALM)
	{
		if(!file_exists(BasicAuth::getAuthFilename($authDomain))) {
			if(!headers_sent()) header('Content-Type: text/plain');
			die('auth file does not exist - you might want to run setup.php');
		}
		// BasicAuth::auth($realm, $authDomain);
		BasicAuth\HTML::auth([$authDomain]);
	}

	/**
	 * happy html bottstrapping
	 * @param string $title
	 * @param string $authDomain
	 * @param string $realm
	 */
	public static function setUpToolbox($title='foomo toolbox', $authDomain = BasicAuth::DEFAULT_AUTH_DOMAIN, $realm = self::BASIC_AUTH_REALM)
	{
		self::auth($authDomain, $realm);
		$doc = HTMLDocument::getInstance()->setTitle($title);

		if(defined('Foomo\\ROOT_HTTP')) {
			$favRoot = \Foomo\ROOT_HTTP . '/img/site/favIcons';
			$doc->setFavIcon($favRoot . '/favicon.ico');
			foreach(array('57', '72', '114') as $size) {
				$size = $size . 'x' . $size;
				$doc->addHead(
					'<link rel="apple-touch-icon" size="' . $size . '" href="' . $favRoot . '/apple-touch-icon-' . $size . '.png" />'
				);
			}
			$doc
				->addStylesheets(array(
					\Foomo\Module::getHtdocsBuildPath('css/fonts.css'),
					\Foomo\Module::getHtdocsBuildPath('css/reset.css'),
					\Foomo\Module::getHtdocsBuildPath('css/app.css'),
					\Foomo\Module::getHtdocsBuildPath('css/module.css')
				))
				->addJavascripts(array(
					\Foomo\ROOT_HTTP . '/js/jquery-1.6.1.min.js',
					\Foomo\Module::getHtdocsBuildPath('js/modules.js')
				))
			;
		}
	}
}
