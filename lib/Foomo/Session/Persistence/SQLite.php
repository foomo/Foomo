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

use Foomo\Module;
use Foomo\Session\PersistorInterface;

/**
 * session persistor using SQLite
 * 
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author uwe <uwe.quitter@bestbytes.com>
 */
class SQLite implements PersistorInterface
{
	/**
	 * session file prefix
	 */
	const DBFILE = 'foomoSessions.sqlite';

	const MAINTAIN_LAST_READ = false;

	private static function getPDO()
	{
		static $pdo;
		if (is_null($pdo)) {
			$savePath = ini_get('session.save_path');
			if (!is_dir($savePath) || !is_writable($savePath)) {
				$savePath = Module::getVarDir('sessions');
				trigger_error('invalid session.save_path - falling back to ' . $savePath, E_USER_WARNING);
			}
			$pdo = new \PDO('sqlite:' . $savePath . DIRECTORY_SEPARATOR . self::DBFILE);
			$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
			$pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
			$pdo->setAttribute(\PDO::ATTR_TIMEOUT, 30);
			$pdo->setAttribute(\PDO::ATTR_PERSISTENT, true);
			//self::createTableIfNotExists();
		}
		return $pdo;
	}

	public function init()
	{
		$pdo = self::getPDO();
		self::createTableIfNotExists($pdo);
	}

	private static function log($text, $sessionId = null)
	{
//		if (!empty($sessionId))
//			file_put_contents(ini_get('error_log'), (empty($sessionId) ? '    ' : substr($sessionId, 0, 4)) . ' - ' . $text . PHP_EOL, FILE_APPEND);
	}

	private static function createTableIfNotExists(\PDO $pdo)
	{
		$sql = "CREATE TABLE IF NOT EXISTS `sessions` (`sessionId` CHAR(48) NOT NULL UNIQUE, `locked` TINYINT(1), `lastWrite` INTEGER, `lastRead` INTEGER, `data` TEXT)";
		if (false === $pdo->exec($sql)) {
			trigger_error(__METHOD__ . ": query '$sql' failed - " . join(', ', $pdo->errorInfo()), E_USER_WARNING);
			return false;
		}
		return true;
	}

	private static function handlePDOException(\PDO $pdo, \PDOException $e)
	{
		$expectedMsg = 'General error: 1 no such table: sessions';
		if (substr($e->getMessage(), -strlen($expectedMsg)) == $expectedMsg) {
			return self::createTableIfNotExists($pdo);
		}
		$expectedMsg = 'General error: 5 database is locked';
		if (substr($e->getMessage(), -strlen($expectedMsg)) == $expectedMsg) {
			self::log('database is locked', ' err');
		}
		return false;
	}

	private static function beginTransaction(\PDO $pdo, $caller)
	{
		try {
			self::log("$caller beginTransaction");
			return self::execute($pdo, $caller, "BEGIN IMMEDIATE TRANSACTION");		// DEFERRED is not enough, leads to "database is locked" errors
																					// even stronger: EXCLUSIVE
		} catch (\PDOException $e) {
			trigger_error("$caller: BEGIN TRANSACTION failed - {$e->getMessage()}", E_USER_WARNING);
			return false;
		}
	}

	private static function endTransaction(\PDO $pdo, $caller, $commit = true)
	{
		try {
			self::log($caller . ' ' . ($commit ? 'commit' : 'rollback'));
			return self::execute($pdo, $caller, $commit ? 'COMMIT' : 'ROLLBACK');
		} catch (\PDOException $e) {
			trigger_error("$caller: " . ($commit ? 'COMMIT' : 'ROLLBACK') . " failed - {$e->getMessage()}", E_USER_WARNING);
			return false;
		}
	}

	private static function commit(\PDO $pdo, $caller)
	{
		return self::endTransaction($pdo, $caller);
	}

	private static function rollback(\PDO $pdo, $caller)
	{
		return self::endTransaction($pdo, $caller, false);
	}

	private static function execute(\PDO $pdo, $caller, $sql, $params = null)
	{
		self::log($sql);
		try {
			if (!is_array($params)) {
				if (false === $pdo->exec($sql)) {
					trigger_error("$caller: '$sql' failed - " . join(', ', $pdo->errorInfo()), E_USER_WARNING);
					return false;
				}
			} else {
				$stmt = $pdo->prepare($sql);
				if (!$stmt->execute($params)) {
					trigger_error("$caller: '$sql' failed - " . join(', ', $stmt->errorInfo()) . ' params=' . var_export($params, true), E_USER_WARNING);
					return false;
				}
			}
		} catch (\PDOException $e) {
			self::handlePDOException($pdo, $e);
			trigger_error("$caller: '$sql' failed - {$e->getMessage()}", E_USER_WARNING);
		}
		return true;
	}

	private static function select(\PDO $pdo, $caller, $sql, $params = null)
	{
		self::log($sql);
		try {
			$stmt = $pdo->prepare($sql);
			if (!$stmt->execute($params)) {
				trigger_error("$caller: '$sql' failed - " . join(', ', $stmt->errorInfo()) . ' params=' . var_export($params, true), E_USER_WARNING);
				return false;
			}
		} catch (\PDOException $e) {
			if (self::handlePDOException($pdo, $e)) {
				return self::select($pdo, $caller, $sql, $params);
			} else {
				trigger_error("$caller: '$sql' failed - {$e->getMessage()}", E_USER_WARNING);
				return false;
			}
		}
		return $stmt->fetchAll(\PDO::FETCH_ASSOC);
	}

	/**
	 * lock or unlock session
	 * @param string $sessionId
	 * @param boolean $lock
	 * @return boolean
	 */
	private function writeLock($sessionId, $lock = true)
	{
		$m = 'writeLock(' . ($lock ? 'lock' : 'release') . ')';
		self::log($m, $sessionId);
		$pdo = self::getPDO();
		if (!self::beginTransaction($pdo, $m)) {
			return false;
		}
		$now = time();
		if ($lock) {
			$sql = "SELECT locked FROM sessions WHERE sessionId=?";
			$results = self::select($pdo, $m, $sql, array($sessionId));
			if (!is_array($results)) {
				self::rollback($pdo, $m);
				return false;
			}
			if (count($results) > 0) {
				if ($results[0]['locked'] != 0) {
					//trigger_error("could not obtain write lock for sessionId: '$sessionId'");
					self::rollback($pdo, $m);
					return false;
				}
			} else {
				$sql = "INSERT INTO sessions (sessionId, locked, lastWrite, lastRead) VALUES (?, 1, $now, ?)";
				if (!self::execute($pdo, $m, $sql, array($sessionId, self::MAINTAIN_LAST_READ ? $now : 0))) {
					self::rollback($pdo, $m);
					return false;
				}
				return self::commit($pdo, $m);
			}
		}
		$sql = "UPDATE sessions SET locked=?, lastWrite=$now, lastRead=? WHERE sessionId=?";
		if (!self::execute($pdo, $m, $sql, array($lock ? 1 : 0, self::MAINTAIN_LAST_READ ? $now : 0, $sessionId))) {
			self::rollback($pdo, $m);
			return false;
		}
		return self::commit($pdo, $m);
	}

	/**
	 * does a session exist?
	 * @param string $sessionId
	 * @return boolean
	 */
	public function exists($sessionId)
	{
		self::log('exists', $sessionId);
		$pdo = self::getPDO();
		$sql = "SELECT count(sessionId) cnt FROM sessions WHERE sessionId=?";
		$results = self::select($pdo, "exists($sessionId)", $sql, array($sessionId));
		if (!is_array($results)) {
			return false;
		}
		return $results[0]['cnt'] > 0;
	}

	/**
	 * destroy a session
	 * @param string $sessionId
	 * @return boolean
	 */
	public function destroy($sessionId)
	{
		self::log('destroy', $sessionId);
		$pdo = self::getPDO();
		$ret = self::execute($pdo, "destroy($sessionId)", "DELETE FROM sessions WHERE sessionId=?", array($sessionId));
		return $ret;
	}

	/**
	 * this has to be an atomic lock across requests and possibly across machines
	 * @param string $sessionId
	 * @return boolean
	 */
	public function lock($sessionId)
	{
		self::log('lock required', $sessionId);
		$ret = $this->writeLock($sessionId, true);
		self::log('lock obtained', $sessionId);
		return $ret;
	}

	/**
	 * load a session
	 * @param string $sessionId
	 * @return \Foomo\Session
	 */
	public function load($sessionId)
	{
		self::log('load', $sessionId);
		$m = "load($sessionId)";
		$pdo = self::getPDO();
		$results = self::select($pdo, $m, "SELECT data FROM sessions WHERE sessionId=?", array($sessionId));
		if (is_array($results)) {
			if (self::MAINTAIN_LAST_READ) {
				self::execute($pdo, $m, "UPDATE sessions SET lastRead=? WHERE sessionId=?", array(time(), $sessionId));
			}
			if (count($results) > 0) {
				$data = $results[0]['data'];
				return empty($data) ? null : unserialize($data);
			}
		}
		return false;
	}

	/**
	 * release the atomic lock from a session
	 * @param string $sessionId
	 * @return boolean
	 */
	public function release($sessionId)
	{
		return $this->writeLock($sessionId, false);
	}

	/**
	 * persist a session
	 * @param string $sessionId
	 * @param \Foomo\Session $session
	 * @return boolean
	 */
	public function persist($sessionId, \Foomo\Session $session)
	{
		self::log('persist', $sessionId);
		$now = time();
		$pdo = self::getPDO();
		$sql = "INSERT OR REPLACE INTO sessions (sessionId, locked, lastWrite, lastRead, data) VALUES (?, 1, $now, $now, ?)";
		$ret = self::execute($pdo, "persist($sessionId)", $sql, array($sessionId, serialize($session)));
		return $ret;
	}

	public static function getAllSessions()
	{
		return self::select(self::getPDO(), 'getAllSessions', "SELECT * FROM sessions");
	}
}