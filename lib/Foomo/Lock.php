<?php

namespace Foomo;

/**
 * generic process locking based on file system locks
 */
class Lock {

	public static $lockHandles = array();

	/**
	 * lock. start the synchronization block
	 * @param string $lockName
	 * @param boolean $blocking
	 * $param object $lockData whatever payload you might want to serialize into the lock file for latter use
	 * @return boolean 
	 */
	public static function lock($lockName, $blocking = true, $lockData = null) {
		$lockFileHandle = self::getLockHandle($lockName);
		if (!$lockFileHandle) {
			return false;
		}
		self::$lockHandles[$lockName] = $lockFileHandle;
		//block till getting the lock
		$lockType = LOCK_EX;
		if ($blocking === false) {
			$lockType = ($lockType | LOCK_NB);
		}
		if (flock($lockFileHandle, $lockType)) { // do an exclusive lock
			//touch(self::getLockFile($lockName));
			self::insertLockData(self::getLockContentsFile($lockName), $lockData);
			return true;
		} else {
			//trigger_error('could not get the lock ' . $lockName, E_USER_WARNING);
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
			//trigger_error('could not find a lock handle for lock ' . $lockName);
			return false;
		} else {
			flock($lockFileHandle, LOCK_UN); // release the lock
			fclose($lockFileHandle);
		unlink(self::getLockFile($lockName));
		//unlink(self::getLockContentsFile($lockName));
			unset(self::$lockHandles[$lockName]);
			
			return true;
		}
	}

	/**
	 * get lock info
	 * @param string $lockName
	 * @return array hash with the following keys: lock_file, lock_age, caller_is_owner, is_locked
	 */
	public static function getLockInfo($lockName) {
		$info = array();
		$info['lock_file'] = self::getLockFile($lockName);
		$info['caller_is_owner'] = self::isLockedByCaller($lockName);
		$info['is_locked'] = self::isLocked($lockName);

		$lockFileContents = self::getLockFileContents($lockName);
		$info['pid'] = $lockFileContents['pid'];
		$info['lockData'] = $lockFileContents['lockData'];
		$info['lock_age'] = ($lockFileContents['timestamp'] !== false && $info['is_locked'] === true) ? time() - intval($lockFileContents['timestamp']) : false;
		return $info;
	}

	private static function getLockFileContents($lockName) {
		$file = self::getLockContentsFile($lockName);
		if (!file_exists($file)) {
			throw new \Exception('file does not exist');
		}
		$contents = file_get_contents($file);
		if ($contents) {
			$contents = unserialize($contents);
		} else {
			$contents = array(
				'pid' => false,
				'timestamp' => false,
				'lockData' => false,
			);
		}
		return $contents;
	}

	/**
	 * get file handle for lockName
	 * @param  string $lockName
	 * @return file handle 
	 */
	private static function getLockHandle($lockName) {
		$lockFile = self::getLockFile($lockName);
		$lockFileHandle = fopen($lockFile, "w+");
		return $lockFileHandle;
	}

	private static function getLockFile($lockName) {
		$baseDir = \Foomo\Config::getVarDir(\Foomo\Module::NAME);
		$lockFile = $baseDir . DIRECTORY_SEPARATOR . '.' . $lockName . '.lock';
		return $lockFile;
	}
	
	private static function getLockContentsFile($lockName) {
		$baseDir = \Foomo\Config::getVarDir(\Foomo\Module::NAME);
		$lockFile = $baseDir . DIRECTORY_SEPARATOR . '.' . $lockName . '.data';
		return $lockFile;
	}


	private static function isLockedByCaller($lockName) {
		return isset(self::$lockHandles[$lockName]);
	}

	private static function isLocked($lockName) {
		if (self::isLockedByCaller($lockName) === true) {
			return true;
		} else {
			//check if somebody else has it
			$canGetLock = self::lock($lockName, $blocking = false);
			self::release($lockName);

			if ($canGetLock) {
				return false;
			} else {
				return true;
			}
		}
	}

	private static function insertLockData($lockContentsFile, $lockData = null) {
		$data = array(
			'pid' => getmypid(),
			'timestamp' => time(),
			'lockData' => $lockData,
		);
		file_put_contents($lockContentsFile, serialize($data));
	}

}