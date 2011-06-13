<?php

namespace Foomo\Session\Persistence;

use Foomo\Session\PersistorInterface;

/**
 * a file system session persistor
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
	public function lock($sessionId)
	{
		$lockFile = self::getFileName($sessionId);
		$this->fps[$sessionId] = fopen($lockFile, 'w');
		if (!flock($this->fps[$sessionId], LOCK_EX)) {
			trigger_error('--- no write lock ---' . $sessionId);
		}
	}
	public function load($sessionId, $reload = false)
	{
		$fileName = self::getFileName($sessionId);
		$contentFileName = self::getContentsFileName($sessionId);
		if (file_exists($fileName)) {
			if (!$this->fps[$sessionId]) {
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
			if ($unserialized === false && !$reload) {
				trigger_error('--- ' . $sessionId . ' WTF session file is empty ' . strlen($fileContents), E_USER_ERROR);
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
			ini_get('session.save_path') . DIRECTORY_SEPARATOR . 
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
	public function persist($sessionId, $sessionObj)
	{
		if ($this->fps[$sessionId]) {
			file_put_contents(self::getContentsFileName($sessionId), serialize($sessionObj) . self::CONTENTS_EOF);
		} else {
			trigger_error('how to write, if the is no fp', E_USER_ERROR);
		}
	}
}