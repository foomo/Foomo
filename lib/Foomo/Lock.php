<?php

namespace Foomo;

/**
 * generic process locking based on file system locks
 *
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author bostjan <bostjan@bestbytes.de>
 */
class Lock {

	private static $lockHandles = array();

	//this allows us to have lock() to be interpreted as a function in php 5.3.2 and not a constructor
	private function __construct() {}
	/**
	 * lock. start the synchronization block
	 *
	 * @param string $lockName
	 * @param boolean $blocking
	 * @param object $lockData whatever payload you might want to serialize into the lock file for latter use
	 *
	 * @return boolean 
	 */
	public static function lock($lockName, $blocking = true, $lockData = null)
	{

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
	 *
	 * @param string $lockName
	 *
	 * @return bool
	 */
	public static function release($lockName)
	{
		$lockFileHandle = false;
		if (isset(self::$lockHandles[$lockName])) {
			$lockFileHandle = self::$lockHandles[$lockName];
		}
		if ($lockFileHandle === false) {
			//trigger_error('could not find a lock handle for lock ' . $lockName);
			return false;
		} else {
			// release the lock
			flock($lockFileHandle, LOCK_UN);
			fclose($lockFileHandle);
			$lockFile = self::getLockFile($lockName);
			if(file_exists($lockFile)) {
				// when locking non without a block sbdy. else might have removed it already
				unlink($lockFile);
			}
			unset(self::$lockHandles[$lockName]);
			return true;
		}
	}

	/**
	 * get lock info
	 * @param string $lockName
	 * @deprecated use getLockInfoObject instead
	 * @return array hash with the following keys: lock_file, lock_age, caller_is_owner, is_locked
	 */
	public static function getLockInfo($lockName)
	{
		$infoObj = self::getLockInfoObject($lockName);
		return array(
			'lock_file' => $infoObj->file,
			'caller_is_owner' => $infoObj->lockedByCaller,
			'is_locked' => $infoObj->locked,
			'pid' => $infoObj->pid,
			'lockData' => $infoObj->data,
			'lock_age' => $infoObj->age
		);
	}

	/**
	 * @param string $lockName
	 * @return Lock\Info
	 */
	public static function getLockInfoObject($lockName)
	{
		$ret = new Lock\Info;
		$ret->file = self::getLockFile($lockName);
		$ret->lockedByCaller = self::isLockedByCaller($lockName);
		$ret->locked = self::isLocked($lockName);
		$lockFileContents = self::getLockFileContents($lockName);
		$ret->pid = $lockFileContents['pid'];
		$ret->data = $lockFileContents['lockData'];
		$ret->age = ($lockFileContents['timestamp'] !== false && $ret->locked) ? time() - intval($lockFileContents['timestamp']) : false;
		return $ret;
	}
	
	/**
	 * check if locked
	 *
	 * @param string $lockName
	 *
	 * @return boolean
	 */
	public static function isLocked($lockName)
	{
		if(file_exists(self::getLockFile($lockName))) {
			if (self::isLockedByCaller($lockName) === true) {
				return true;
			} else {
				//check if somebody else has it
				$canGetLock = self::lock($lockName, $blocking = false);
				if($canGetLock) {
					self::release($lockName);
				}
				if ($canGetLock) {
					return false;
				} else {
					return true;
				}
			}
		} else {
			return false;
		}
	}

	private static function getLockFileContents($lockName)
	{
		$file = self::getLockContentsFile($lockName);
		if (!file_exists($file)) {
			$contents = false;
		} else {
			$contents = file_get_contents($file);
		}
		
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
	 *
	 * @return resource file handle
	 */
	private static function getLockHandle($lockName)
	{
		$lockFile = self::getLockFile($lockName);

		//@todo: this is a hack - flock otherwise dies randomly on NFS for docker for mac !!!
		if (\Foomo\Config::getMode() == \Foomo\Config::MODE_TEST) {
			touch($lockFile);
		}
		// end of hack

		$lockFileHandle = fopen($lockFile, "w+");
		return $lockFileHandle;
	}

	private static function getLockFile($lockName)
	{
		$baseDir = \Foomo\Config::getVarDir(\Foomo\Module::NAME);
		$lockFile = $baseDir . DIRECTORY_SEPARATOR . '.' . $lockName . '.lock';
		return $lockFile;
	}
	
	private static function getLockContentsFile($lockName)
	{
		$baseDir = \Foomo\Config::getVarDir(\Foomo\Module::NAME);
		$lockFile = $baseDir . DIRECTORY_SEPARATOR . '.' . $lockName . '.data';
		return $lockFile;
	}

	private static function isLockedByCaller($lockName)
	{
		return isset(self::$lockHandles[$lockName]);
	}

	private static function insertLockData($lockContentsFile, $lockData = null)
	{
		$data = array(
			'pid' => getmypid(),
			'timestamp' => time(),
			'lockData' => $lockData,
		);
		file_put_contents($lockContentsFile, serialize($data));
	}

}