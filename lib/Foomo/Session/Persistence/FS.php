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

namespace Foomo\Session\Persistence;

use Foomo\Session\PersistorInterface;

/**
 * a file system session persistor
 * 
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
class FS implements PersistorInterface {

	private $fps = array();
	/**
	 * session file prefix
	 */
	const PREFIX = 'foomoSession';
	/**
	 * session content file
	 */
	const CONTENTS_POSTFIX = 'contents';

	const CONTENTS_EOF = '<<<<<<<<<<<<<<<<<<<<<<THE_END';

	public function exists($sessionId)
	{
		return file_exists(self::getFileName($sessionId));
	}

	public function destroy($sessionId)
	{
		$files = array(
			self::getFileName($sessionId),
			self::getContentsFileName($sessionId)
		);
		foreach ($files as $file) {
			if (file_exists($file)) {
				unlink($file);
			}
		}
	}
	private static function getSavePath()
	{
		static $savePath;
		if(is_null($savePath)) {
			$savePath = ini_get('session.save_path');
			if(!is_dir($savePath) || !is_writable($savePath)) {
				$savePath = \Foomo\Module::getVarDir('sessions');
				trigger_error('invalid session.save_path - falling back to ' . $savePath, E_USER_WARNING);
			}
		}
		return $savePath;
	}
	public function lock($sessionId)
	{
		$lockFile = self::getFileName($sessionId);
		$this->fps[$sessionId] = fopen($lockFile, 'w');
		if (!flock($this->fps[$sessionId], LOCK_EX)) {
			$savePath = self::getSavePath();
			if(!is_dir($savePath)) {
				trigger_error('session save path "' . $savePath . '" does not exist', E_USER_ERROR);
			} elseif(!is_writable($savePath)) {
				trigger_error('session save path "' . $savePath . '" is not writable', E_USER_ERROR);
			} else {
				trigger_error('could not obtain no write lock for sessionId: "' . $sessionId . '"');
			}
		}
	}
	public function load($sessionId)
	{
		$fileName = self::getFileName($sessionId);
		$contentFileName = self::getContentsFileName($sessionId);
		if (file_exists($fileName)) {
			if (!isset($this->fps[$sessionId])) {
				// which means I have not locked this one
				/*
				 * this is a safe, but blocking way, to do things
					$fp = fopen($fileName, 'w');
					if (!flock($fp, LOCK_SH)) {
						trigger_error('--- lock read failed !!! ---' . $sessionId, E_USER_ERROR);
					}
					if(!empty($_GET['s'])) {
						sleep($_GET['s']);
					}
					$unserialized = $this->loadSessionFromFs($contentFileName);
					fclose($fp);
				*/
				$unserialized = $this->loadSessionFromFs($contentFileName, true);
			} else {
				$unserialized = $this->loadSessionFromFs($contentFileName);
			}
			if($unserialized !== false) {
				return $unserialized;
			}
		}
	}
	public function release($sessionId)
	{
		if (isset($this->fps[$sessionId])) {
			fclose($this->fps[$sessionId]);
			$this->fps[$sessionId] = null;
		}
	}
	private function loadSessionFromFs($contentFileName, $tryHard = false)
	{
		if(file_exists($contentFileName)) {
			if($tryHard) {
				// a non locking read only hack ...
				$success = false;
				$numberOfTries = 0;
				while($numberOfTries < 10) {
					if($numberOfTries > 0) {
						usleep(200000);
					}
					$fileContents = file_get_contents($contentFileName);
					if(substr($fileContents, - strlen(self::CONTENTS_EOF))) {
						$success = true;
						break;
					} else {
						trigger_error('retrying');
					}
					$numberOfTries ++;
				}
				if(!$success) {
					trigger_error('failed to read from session file ' . $contentFileName, E_USER_ERROR);
				}
			} else {
				$fileContents = file_get_contents($contentFileName);
			}
			if (strlen($fileContents) > 0) {
				return unserialize(substr($fileContents, 0, strlen($fileContents) - strlen(self::CONTENTS_EOF)));
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	/**
	 * get a session lock file name
	 *
	 * @internal
	 *
	 * @param string $sessionId
	 *
	 * @return string
	 */
	public static function getFileName($sessionId)
	{
		return
			self::getSavePath() . DIRECTORY_SEPARATOR .
			self::PREFIX . '-' . $sessionId
		;
	}
	/**
	 * get a session lock file name
	 *
	 * @internal
	 *
	 * @param string $sessionId
	 *
	 * @return string
	 */
	public static function getContentsFileName($sessionId)
	{
		return self::getFileName($sessionId) . '-' . self::CONTENTS_POSTFIX;
	}
	/**
	 * get a session content file name
	 *
	 * @internal
	 *
	 * @param string $sessionId
	 *
	 * @return string
	 */
	public function persist($sessionId, \Foomo\Session $session)
	{
		if ($this->fps[$sessionId]) {
			file_put_contents(self::getContentsFileName($sessionId), serialize($session) . self::CONTENTS_EOF);
		} else {
			trigger_error('how to write, if the is no fp', E_USER_ERROR);
		}
	}
}
