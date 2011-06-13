<?php

/*
 * bestbytes-copyright-placeholder
 */

namespace Foomo\Session;

use Foomo\Session;

/**
 * utility class
 * 
 * @internal 
 */
class GC implements GCPrinterInterface {
	const LIVE_SESSION_TIMEOUT = 120;
	const MAX_BROWSER_SESSION_LIFETIME = 86400;

	public $output;
	
	const DATE_FORMAT = 'Y-m-d H:i:s';
	
	public function __construct($flushOutput = true, $verbose = false)
	{
		if (!$flushOutput) {
			$this->output = __CLASS__ . ' garbage collection :' . PHP_EOL . PHP_EOL;
		}
	}

	public static function run()
	{
		$inst = new self;
		$inst->runVerboseGC();
	}
	private function runVerboseGC() {
		$sessionConfig = Session::getConf();
		if (!is_null($sessionConfig)) {
			$persistorGCClass = __NAMESPACE__ . '\\Persistence\\GC\\' . $sessionConfig->persistor;

			$persistorGC = new $persistorGCClass($this);
			$startTime = microtime(true);

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
			$this->out('starting GC in session.save_path : "' . ini_get('session.save_path') . '"');
			$this->out('session.gc_maxlifetime : "' . $gcLifeTime . '", session.cookie_lifetime : "' . $cookieLifetime . '"');
			$this->out('=> max lifeTime for a session files : "' . $lifeTime . '"');
			ini_set('html_errors', 'Off');
			foreach($persistorGC as $sessionItem) {
				/* @var $sessionItem Persistence\GC\Item */
				$this->out($sessionItem->sessionId);
				
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

			$this->out('Live Sessions (time since last access < ' . self::LIVE_SESSION_TIMEOUT . ' s) :');
			$i = 1;
			foreach ($liveSessions as $sessionItem) {
				$this->out('  ' . $i . ' ' . $sessionItem->sessionId);
				$i++;
			}


			$this->out('Active sessions : ');
			$i = 1;
			foreach ($activeSessions as $sessionItem) {
				$this->out('  ' . ($i++) . ' ' . $sessionItem->sessionId);
			}

			$this->out('Old Sessions : ');
			$i = 1;
			foreach ($oldSessions as $sessionItem) {
				$this->out('  ' . ($i++) . ' ' . $sessionItem->sessionId);
				Session::$persistor->destroy($sessionItem->sessionId);
			}
			$this->out('done in ' . (microtime(true) - $startTime) . ' seconds');
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
