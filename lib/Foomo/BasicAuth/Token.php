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
class Token
{
	const MAX_LIFE_TIME = 600;
	const FILE_PREFIX = "token-";
	/**
	 * @param string $user
	 * @param string[] $domains
	 *
	 * @return string password
	 */
	public static function createTokenForUser($user, array $domains)
	{
		$password = uniqid(uniqid("", true), true);
		file_put_contents(self::getFilename($user, $password), serialize($domains));
		return $password;
	}

	private static function getFilename($user, $password)
	{
		$res = \Foomo\Module::getTokenDirResource();
		$folder = $res->getFileName();
		if(!file_exists($folder)) {
			$res->tryCreate();
		}
		return $folder . DIRECTORY_SEPARATOR . self::FILE_PREFIX . self::hash($user, $password);
	}

	public static function hash($user, $password)
	{
		return hash("sha256", $user.$password, false);
	}

	/**
	 * @param $user
	 * @param $password
	 * @param int $maxAge token must not be older than $maxAge seconds
	 *
	 * @return string[] domains
	 */
	public static function useToken($user, $password, $maxAge = self::MAX_LIFE_TIME)
	{
		$filename = self::getFilename($user, $password);
		$domains = [];
		if(file_exists($filename)) {
			$ctime = filectime($filename);
			if(time() - $ctime < $maxAge) {
				$d = unserialize(file_get_contents($filename));
				if(is_array($d)) {
					$domains = $d;
				}
			}
			unlink($filename);
		}
		return $domains;
	}
}