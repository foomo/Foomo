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

namespace Foomo\BasicAuth\Token;

/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author Jan Halfar jan@bestbytes.com
 */
class GC extends \Foomo\Jobs\AbstractJob
{
	protected $executionRule = '0	*	*	*	*';
	public function getId()
	{
		return sha1(__CLASS__);
	}
	public function getDescription()
	{
		return 'collects expired bashic auth tokens';
	}
	public function run() {
		echo implode(PHP_EOL, self::collectGarbage(\Foomo\BasicAuth\Token::MAX_LIFE_TIME));
	}
	public static function collectGarbage($maxAge) {
		$removed = [];
		$maxCreationTime = time() - $maxAge;
		$res = \Foomo\Module::getTokenDirResource();
		if(!file_exists($res->getFileName())) {
			$res->tryCreate();
		}
		foreach(new \DirectoryIterator($res->getFileName()) as $fileInfo) {
			if($fileInfo->isFile() && substr($name = $fileInfo->getFilename(), 0, strlen(\Foomo\BasicAuth\Token::FILE_PREFIX)) == \Foomo\BasicAuth\Token::FILE_PREFIX) {
				if($fileInfo->getCTime() < $maxCreationTime) {
					unlink($fileInfo->getPathname());
					$removed[] = $name;
				}
			}
		}
		return $removed;
	}
}