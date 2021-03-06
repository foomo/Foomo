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

namespace Foomo\Session;

use Foomo\Session;

/**
 * utility class
 * 
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 * @internal
 */
class GC implements GCPrinterInterface {
	const LIVE_SESSION_TIMEOUT = 120;
	const MAX_BROWSER_SESSION_LIFETIME = 86400;

	public $output;

	const DATE_FORMAT = 'Y-m-d H:i:s';

	public function __construct($flushOutput = true)
	{
		if (!$flushOutput) {
			$this->output = __CLASS__ . ' garbage collection :' . PHP_EOL . PHP_EOL;
		}
	}
	public static function run($flushOutput = true)
	{
		$inst = new self($flushOutput);
		$inst->runVerboseGC();
		if(!$flushOutput) {
			return $inst->output;
		}
	}
	private function runVerboseGC()
	{
		$sessionConfig = Session::getConf();
		if (!is_null($sessionConfig)) {
			$persistorGCClass = __NAMESPACE__ . '\\Persistence\\GC\\' . $sessionConfig->persistor;

			$persistorGC = new $persistorGCClass($this);
			$startTime = microtime(true);

			$gcLifeTime = ini_get('session.gc_maxlifetime');
			$cookieLifetime = ini_get('session.cookie_lifetime');
			$sessionSavePath = ini_get('session.save_path');
			if ($cookieLifetime == 0) {
				$lifeTime = self::MAX_BROWSER_SESSION_LIFETIME;
			} else {
				$lifeTime = $cookieLifetime;
			}
			$oldSessions = array();
			$activeSessions = array();
			$liveSessions = array();
			$this->out("starting GC in session.save_path: '$sessionSavePath'");
			$this->out("session.gc_maxlifetime: $gcLifeTime, session.cookie_lifetime: $cookieLifetime");
			$this->out("=> max lifeTime for a session: {$lifeTime}s");
			ini_set('html_errors', 'Off');

			foreach ($persistorGC as $sessionItem) {
				/* @var $sessionItem Persistence\GC\Item */
				if (time() - $sessionItem->lastReadAccess > $lifeTime) {
					$oldSessions[] = $sessionItem;
				} else {
					if ($startTime - $sessionItem->lastReadAccess < self::LIVE_SESSION_TIMEOUT) {
						$liveSessions[] = $sessionItem;
					} else {
						$activeSessions[] = $sessionItem;
					}
				}
			}

			$this->out('Live Sessions (time since last access < ' . self::LIVE_SESSION_TIMEOUT . ' s):');
			foreach ($liveSessions as $i => $sessionItem) {
				$this->out(sprintf('%6d %s', 1+$i, $sessionItem->sessionId));
			}
			$this->out('Active sessions: ');
			foreach ($activeSessions as $i => $sessionItem) {
				$this->out(sprintf('%6d %s', 1+$i, $sessionItem->sessionId));
			}
			$this->out('Deleting old sessions: ');
			foreach ($oldSessions as $i => $sessionItem) {
				$this->out(sprintf('%6d %s', 1+$i, $sessionItem->sessionId));
				Session::$persistor->destroy($sessionItem->sessionId);
			}

			$this->out(sprintf('done in %.3f ms', 1000.0*(microtime(true) - $startTime)));
		} else {
			$this->out('session is not configured - no garbage to collect');
		}
	}

	public function out($line)
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
