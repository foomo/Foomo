<?php

namespace Foomo;
/**
 * generic process locking based on file system locks
 */
class Lock {
	private static $lockHandles = array();
	/**
	 * lock. start the synchronization block
	 * @param string $lockName
	 * @param boolean $blocking
	 * @return boolean 
	 */
	public static function lock($lockName, $blocking = true) {
		$lockFileHandle = self::getLockHandle($lockName);
		self::$lockHandles[$lockName] = $lockFileHandle;
		//block till getting the lock
		$lockType = LOCK_EX;
		if ($blocking === false) {
			$lockType = ($lockType | LOCK_NB);
		}
		
		
		
		if (flock($lockFileHandle, $lockType)) { // do an exclusive lock
			
			return true;
		} else {
			trigger_error('could not get the lock ' . $lockName, E_USER_WARNING);
			return false;
		}
	}

	/**
	 * release. end the synchronization block
	 * @param type $lockName 
	 */
	public static function release($lockName) {
		$lockFileHandle = false;
		if (isset(self::$lockHandles[$lockName])) {
			$lockFileHandle = self::$lockHandles[$lockName];
		}
		if ($lockFileHandle === false) {
			trigger_error('could not find a lock handle for lock ' . $lockName);
			return false;
		} else {
			flock($lockFileHandle, LOCK_UN); // release the lock
			fclose($lockFileHandle);
			unset(self::$lockHandles[$lockName]);
			return true;
		}
	}
	
	public function getLockInfo($lockName) {
		$info = array();
		$info['lock_file'] = self::getLockFile($lockName);
		$info['lock_age'] = self::getLockAge($info['lock_file']);
		return $info;
	}
	
	
	/**
	 * get file handle for lockName
	 * @param  string $lockName
	 * @return file handle 
	 */
	private static function getLockHandle($lockName) {
		$lockFile = self::getLockFile($lockName);
		$lockFileHandle = fopen($lockFile, "w");
		return $lockFileHandle;
	}	
	
	private static function getLockFile($lockName) {
		$baseDir = \Foomo\Config::getVarDir(\Foomo\Module::NAME);
		$lockFile = $baseDir . DIRECTORY_SEPARATOR . '.' . $lockName . '.lock';
		return $lockFile;
	}
	
	private static function getLockAge($lockFile) {
		clearstatcache();
		return time() - filectime($lockFile);
	}
	
	
	
}