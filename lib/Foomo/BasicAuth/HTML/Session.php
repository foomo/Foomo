<?php
/*
 * This file is part of the foomo Opensource Framework.
 * 
 * The foomo Opensource Framework is free software: you can redistribute it
 * and/or modify it under the terms of the GNU Lesser General Public License as
 * published  by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 * 
 * The foomo Opensource Framework is distributed in the hope that it will
 * be useful, but WITHOUT ANY WARRANTY; without even the implied warranty
 * of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public License along with
 * the foomo Opensource Framework. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Foomo\BasicAuth\HTML;

/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan
 */

final class Session
{
	private $user = null;
	private $domains = [];

	/**
	 * @return self
	 */
	private static function getInstance()
	{
		return \Foomo\Session::getClassInstance(__CLASS__);
	}
	public static function getUser()
	{
		return self::getInstance()->user;
	}
	public static function getDomains()
	{
		return self::getInstance()->domains;
	}
	public static function reset()
	{
		\Foomo\Session::lockAndLoad();
		$inst = self::getInstance();
		$inst->user = null;
		$inst->domains = [];

	}
	public static function setUser($user, $domains)
	{
		\Foomo\Session::lockAndLoad();
		$inst = self::getInstance();
		if($user != $inst->user) {
			$inst->domains = [];
		}
		$inst->user = $user;
		$inst->domains = array_unique(array_merge($inst->domains,  $domains));
	}
	public static function userIsAuthenticatedForOneDomain($domains)
	{
		return count(array_intersect($domains, self::getInstance()->getDomains())) > 0;
	}
	public static function userIsAuthenticatedForDomain($domain)
	{
		return in_array($domain, self::getInstance()->domains);
	}
}