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

namespace Foomo\BasicAuth;

/**
 * basic auth file CRUD
 *
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
class HTML
{
	public static function auth(array $domains, $authMVCClassOrObject = "Foomo\\BasicAuth\\HTML\\Frontend")
	{
		if(!self::isAvailable() || (!empty($_SERVER["PHP_AUTH_USER"]) && !empty($_SERVER["PHP_AUTH_PW"]))) {
			// basic auth fallback
			\Foomo\BasicAuth::auth("authenticate", $domains[0]);
		} else {
			if(!HTML\Session::userIsAuthenticatedForOneDomain($domains)) {
				if(is_string($authMVCClassOrObject)) {
					$authMVCClassOrObject = new $authMVCClassOrObject($domains);
				}
				echo \Foomo\MVC::run($authMVCClassOrObject, $_SERVER["REQUEST_URI"], true);
				exit;
			}
		}
	}
	public static function login($user, $password, $domains)
	{
		$authenticatedDomains = [];
		foreach($domains as $domain) {
			if(\Foomo\BasicAuth::checkCredentialsForDomain($user, $password, $domain)) {
				$authenticatedDomains[] = $domain;
			}
		}
		if(count($authenticatedDomains) > 0) {
			\Foomo\MVC::abort();
			\Foomo\BasicAuth\HTML\Session::setUser($user, $authenticatedDomains);
			header('Location: ' . $_SERVER["REQUEST_URI"]);
			exit;
		}
	}
	public static function isAvailable()
	{
		return \Foomo\Session::getEnabled();
	}
	public static function logout()
	{
		HTML\Session::reset();
	}
}