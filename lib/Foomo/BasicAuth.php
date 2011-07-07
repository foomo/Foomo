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
 * provides a simple interface for basic http authentication
 * 
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
class BasicAuth {

	private $authUserFile;
	private $passwordEncryption;
	private $authenticated = false;

	const DEFAULT_AUTH_DOMAIN = 'default';

	/**
	 * construct your auth object
	 *
	 * @param string $authUserDomain filename of the user auth file in \Foomo\ROOT . '/var/basicAuth'
	 * @param string $passwordEncryption password hashing algorythm CRYPT | MD5 | SHA | PLAINTEXT
	 * @param boolean $SSLRequireSSL
	 *
	 * @return boolean
	 */
	public function __construct($authUserDomain=null, $passwordEncryption = 'crypt', $SSLRequireSSL = true)
	{
		if (is_null($authUserDomain)) {
			if (file_exists(self::getDefaultAuthFilename())) {
				$authUserFile = self::getDefaultAuthFilename();
			}
		} else {
			$authUserFile = self::getAuthFilename($authUserDomain);
		}
		if (!file_exists($authUserFile)) {
			trigger_error('no auth file present >' . $authUserFile . '<', E_USER_ERROR);
			exit(1);
		}
		$this->authUserFile = $authUserFile;
		$this->passwordEncryption = $passwordEncryption;
	}

	/**
	 * name of the default auth file
	 *
	 * @return string
	 */
	public static function getDefaultAuthFilename()
	{
		return self::getAuthFilename(self::DEFAULT_AUTH_DOMAIN);
	}

	public static function getAuthFilename($domain)
	{
		// is that secure enough ?!
		$domain = \basename($domain);
		return self::getAuthDirname() . \DIRECTORY_SEPARATOR . $domain;
	}

	/**
	 * name of the auth dir name
	 *
	 * @internal
	 *
	 * @return string
	 */
	public static function getAuthDirname()
	{
		return Config::getVarDir() . DIRECTORY_SEPARATOR . 'basicAuth';
	}

	/**
	 * check authentication statically - static shortcut to authenticate
	 *
	 * @param string $realm the realm message, taht will typically displayed in the browsers auth dialog
	 * @param string $authUserDomain full filename of the user auth file
	 * @param string $passwordEncryption CRYPT | MD5 | SHA | PLAINTEXT
	 * @param boolean $SSLRequireSSL
	 *
	 * @return boolean
	 */
	public static function auth($realm = 'authentication', $authUserDomain = null, $passwordEncryption = 'crypt', $SSLRequireSSL = true)
	{
		$auth = new self($authUserDomain, $passwordEncryption, $SSLRequireSSL);
		return $auth->authenticate($realm);
	}

	/**
	 * Call this to authenticate the user - will only return true - if the user is not authenticated the program will terminate, since other than that the headers can not be sent
	 * If you really just want to check, use @see checkAuthentication
	 *
	 * @return bool
	 */
	public function authenticate($realm = 'authentication')
	{
		if (!$this->checkAuthentication()) {
			header('HTTP/1.0 401 Unauthorized');
			header('WWW-Authenticate: Basic realm=' . $realm);
			exit;
		} else {
			return true;
		}
	}

	/**
	 * check the authentication - if you need to force it, use @see authenticate
	 *
	 * @return boolean
	 */
	public function checkAuthentication()
	{
		if ($this->authenticated) {
			return true;
		} else {
			$auth = false;
			if (isset($_SERVER["PHP_AUTH_USER"]) && $_SERVER["PHP_AUTH_PW"]) {
				$fp = fopen($this->authUserFile, 'r');
				//$file_contents = fread($fp, filesize($this->authUserFile));
				// Split each of the lines into a username and a password pair
				// and attempt to match them to $PHP_AUTH_USER and $PHP_AUTH_PW.
				//			foreach ($lines as $line) {
				while ($line = fgets($fp)) {
					$line = trim($line);
					list($username, $password) = explode(':', $line);
					if ($username == $_SERVER['PHP_AUTH_USER']) {
						switch ($this->passwordEncryption) {
							case 'crypt':
								// Get the salt from $password. It is always the first
								// two characters of a DES-encrypted string.
								$salt = substr($password, 0, 2);
								// Encrypt $PHP_AUTH_PW based on $salt
								$enc_pw = crypt($_SERVER["PHP_AUTH_PW"], $salt);
								break;
						}
						if ($password == "$enc_pw") {
							// A match is found, meaning the user is authenticated.
							// Stop the search.
							$auth = true;
							break;
						}
					}
				}
				fclose($fp);
			}
			if (!$auth) {
				return false;
			} else {
				$this->authenticated = true;
				return true;
			}
		}
	}

	/**
	 * check if you are authenticated use @see checkAuthentication instead
	 *
	 * @deprecated
	 * @return boolean
	 */
	public function getAuthenticated()
	{
		return $this->checkAuthentication();
	}

	public function logout()
	{

	}

}