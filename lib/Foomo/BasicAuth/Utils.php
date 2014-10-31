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
class Utils
{
	//---------------------------------------------------------------------------------------------
	// ~ Public static methods
	//---------------------------------------------------------------------------------------------

	/**
	 * get hash table of users / password hashes
	 *
	 * @param string $domain
	 * @return hash array('user1' => 'hash1', 'user2' => 'hash2', ...)
	 */
	public static function getUsers($domain)
	{
		$users = array();
		$authFilename = \Foomo\BasicAuth::getAuthFilename($domain);
		if(file_exists($authFilename)) {
			$rawUsers = explode(chr(10), file_get_contents($authFilename));

			foreach($rawUsers as $line) {
				$line = trim($line);
				if(empty($line)) {
					continue;
				}
				$parts = explode(':', $line);
				$users[$parts[0]] = $parts[1];
			}
		}
		return $users;
	}

	/**
	 * auth domain files
	 *
	 * @return string
	 */
	public static function getDomains()
	{
		$ret = array();
		$dirIterator = new \DirectoryIterator(\Foomo\BasicAuth::getAuthDirname());
		foreach($dirIterator as $fileInfo) {
			/* @var $fileInfo SplFileInfo */
			if(!$fileInfo->isDir() && !$fileInfo->isDot() && $fileInfo->isFile()) {
				$ret[] = $fileInfo->getBasename();
			}
		}
		return $ret;
	}

	/**
	 * update / create (if does not exist) user
	 *
	 * @param string $domain
	 * @param string $name
	 * @param string $password
	 * @param string $hashAlgorythm so far crypt only
	 * @return boolean
	 */
	public static function updateUser($domain, $name, $password, $hashAlgorythm = 'sha')
	{
		$users = self::getUsers($domain);
		$users[$name] = self::hash($password, $hashAlgorythm);
		return self::saveUsers($domain, $users);
	}

	/**
	 * delete user in a domain
	 *
	 * @param string $domain
	 * @param string $user
	 * @return boolean
	 */
	public static function deleteUser($domain, $user)
	{
		$users = self::getUsers($domain);
		unset($users[$user]);
		return self::saveUsers($domain, $users);
	}

	/**
	 * delete a domain
	 *
	 * @param string $domain
	 * @return boolean
	 */
	public static function deleteDomain($domain)
	{
		return \unlink(\Foomo\BasicAuth::getAuthFilename($domain));
	}

	/**
	 * create an auth domain file
	 *
	 * @param type $domain
	 * @return boolean
	 */
	public static function createDomain($domain)
	{
		return \touch(\Foomo\BasicAuth::getAuthFilename($domain));
	}

	/**
	 * hash a password into an authfile
	 *
	 * @param string $password
	 * @param string $algorythm so far only crypt is supported
	 * @param string $salt
	 *
	 * @return string
	 */
	public static function hash($password, $algorythm = 'sha', $salt = null)
	{
		switch($algorythm) {
			case 'sha':
				$hash = '{SHA}' . base64_encode(sha1($salt . $password, true));
				break;
			case 'crypt':
				trigger_error('please do not use crypt anymore', E_USER_DEPRECATED);
				if(is_null($salt)) {
					$salt = self::getSaltChar() . self::getSaltChar();
				}
				$hash = crypt($password, $salt);
				break;
			default:
				trigger_error('unsopported hasing algorythm ' . $algorythm, E_USER_ERROR);
		}
		return $hash;
	}

	//---------------------------------------------------------------------------------------------
	// ~ Private static methods
	//---------------------------------------------------------------------------------------------

	/**
	 * @param string $domain
	 * @param array $users
	 * @return boolean
	 */
	private static function saveUsers($domain, $users)
	{
		$fp = fopen(\Foomo\BasicAuth::getAuthFilename($domain), 'w');
		if($fp === false) {
			return false;
		} else {
			foreach($users as $name => $hash) {
				fwrite($fp, $name . ':' . $hash . chr(10));
			}
			fclose($fp);
			return true;
		}
	}

	/**
	 * @return string
	 */
	private static function getSaltChar()
	{
		if(rand(0,1)) {
			return chr(rand(65, 90));
		} else {
			return chr(rand(97, 122));
		}
	}
}