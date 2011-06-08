<?php

/*
 * bestbytes-copyright-placeholder
 */

namespace Foomo\Session;

use Foomo\Session;
use SplFileInfo;
use DirectoryIterator;

/**
 * utility class
 * 
 * @internal 
 */
class Utils {
	const LIVE_SESSION_TIMEOUT = 120;
	const MAX_BROWSER_SESSION_LIFETIME = 86400;

	public $output;

	public function __construct($flushOutput = true, $verbose = false)
	{
		if (!$flushOutput) {
			$this->output = __CLASS__ . ' garbage collection :' . PHP_EOL . PHP_EOL;
		}
	}

	public function collectGarbage()
	{
		$sessionConfig = Session::getConf();
		if (!is_null($sessionConfig)) {
			if ($sessionConfig->type != 'foomo') {
				$this->out('can not handle garbage collection for ' . $sessionConfig->type . ' aborting ');
			} else {
				$dateFormat = 'Y-m-d H:i:s';
				$startTime = microtime(true);

				$sessionDir = ini_get('session.save_path');
				$directoryIterator = new DirectoryIterator($sessionDir);
				$now = time();
				$gcLifeTime = ini_get('session.gc_maxlifetime');
				$cookieLifetime = ini_get('session.cookie_lifetime');
				if ($cookieLifetime == 0) {
					$lifeTime = self::MAX_BROWSER_SESSION_LIFETIME;
				} else {
					$lifeTime = $cookieLifetime;
				}
				$oldSessions = array();
				$activeSessions = array();
				$liveSessions = array();
				$this->out('starting GC in session.save_path : "' . $sessionDir . '"');
				$this->out('session.gc_maxlifetime : "' . $gcLifeTime . '", session.cookie_lifetime : "' . $cookieLifetime . '"');
				$this->Out('=> max lifeTime for a session files : "' . $lifeTime . '"');

				foreach ($directoryIterator as $file) {
					/* @var $file DirectoryIterator */
					if ($file->isFile()) {
						$baseName = $file->getBasename();
						if (strpos($baseName, Session::PREFIX) === 0 && substr($baseName, -8) == Session::CONTENTS_POSTFIX) {
							// skipping contents file
							continue;
						}
						if (strpos($baseName, Session::PREFIX) === 0) { // && substr($baseName, -8) == 'contents') {
							$sessionId = substr($baseName, strlen(Session::PREFIX) + 1);
							$contentsFile = Session::foomoGetContentsFileName($sessionId);
							if (file_exists($contentsFile)) {
								$contentsFileExists = true;
								$file = new SplFileInfo($contentsFile);
							} else {
								$contentsFileExists = false;
							}
							$aTime = $file->getATime();
							$timeSinceLastAccess = $now - $aTime;
							$sessionInfo = $sessionId . ' timeSinceLastAccess : ' . $timeSinceLastAccess . ', aTime : ' . date($dateFormat, $file->getATime()) . ', cTime : ' . date($dateFormat, $file->getCTime());
							self::out($sessionInfo);

							if ($contentsFileExists) {
								if ($timeSinceLastAccess > $lifeTime) {
									$oldSessions[] = $sessionId;
								} else {
									if ($timeSinceLastAccess < self::LIVE_SESSION_TIMEOUT) {
										$liveSessions[] = $sessionInfo;
									} else {
										$activeSessions[] = $sessionId;
									}
								}
							} else {
								if ($timeSinceLastAccess > $gcLifeTime) {
									$oldSessions[] = $sessionId;
								} else {
									if ($timeSinceLastAccess < self::LIVE_SESSION_TIMEOUT) {
										$liveSessions[] = $sessionInfo;
									} else {
										$activeSessions[] = $sessionId;
									}
								}
							}
						}
					}
				}
				$this->out('Live Sessions (time since last access < ' . self::LIVE_SESSION_TIMEOUT . ' s) :');
				$i = 1;
				foreach ($liveSessions as $liveSession) {
					$this->out('  ' . $i . ' ' . $liveSession);
					$i++;
				}


				$this->out('Active sessions : ');
				$i = 1;
				foreach ($activeSessions as $activeSession) {
					$this->out('  ' . ($i++) . ' ' . $activeSession);
				}

				$this->out('Old Sessions : ');
				;
				$i = 1;
				foreach ($oldSessions as $oldSession) {
					$this->out('  ' . ($i++) . ' ' . $oldSession);
					;
					$sessionLockFile = Session::foomoGetFileName($oldSession);
					$sessionContentsFile = Session::foomoGetContentsFileName($oldSession);
					$this->unlinkSessionFile($sessionLockFile);
					$this->unlinkSessionFile($sessionContentsFile);
				}
				$this->out('done in ' . (microtime(true) - $startTime) . ' seconds');
			}
		} else {
			$this->out('session is not configured - no garbage to collect');
		}
	}

	private function unlinkSessionFile($fileName)
	{
		if ((!file_exists($fileName) || !is_writable($fileName)) || !unlink($fileName)) {
			if (file_exists($fileName)) {
				$this->out('    can not unlink ' . $fileName);
			}
		} else {
			$this->out('    unlinked ' . $fileName);
			;
		}
	}

	private function out($line)
	{
		if (empty($this->output)) {
			echo $line . PHP_EOL;
			ob_flush();
			flush();
		} else {
			$this->output .= $line . PHP_EOL;
		}
	}

}
