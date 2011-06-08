<?php

/*
 * bestbytes-copyright-placeholder
 */

namespace Foomo;

/**
 * session that provides the ability to run a (non) blocking session
 */
class Session {
	/**
	 * session prefix
	 */
	const PREFIX = 'foomoSession';
	const CONTENTS_POSTFIX = 'contents';
	/**
	 * keeps all the class instances
	 *
	 * @var array
	 */
	private $instances = array();
	/**
	 * singleton
	 *
	 * @var Foomo\Session
	 */
	private static $instance;
	/**
	 * my session id
	 *
	 * @var string
	 */
	private $sessionId;
	/**
	 * hash of client informations
	 *
	 * @var string
	 */
	private $clientHash;
	/**
	 * start time of the session
	 *
	 * @var integer
	 */
	private $startTime;
	private $age = 0;
	private $type;
	/**
	 * session is dirty and needs to be written
	 *
	 * @var string
	 */
	private $dirty = false;
	private function __construct()
	{
		$this->clientHash = self::getClientHash();
		$this->startTime = time();
	}

	public function __wakeUp()
	{
		if (is_null($this->age)) {
			$this->age = 0;
		}
		$this->age++;
		//trigger_error('wake up ' . $this->sessionId . ' @ age ' . $this->age);
	}

	/**
	 * get a session persistent instance of a class - singleton style
	 *
	 * @param string $className
	 * @param string $identifier multiton
	 *
	 * @return stdClass
	 */
	public static function getClassInstance($className, $identifier = 'defaultInstance')
	{
		$lowerClassName = strtolower($className);
		$inst = self::getInstance();
		$key = $lowerClassName . $identifier;
		if (!isset($inst->instances[$key])) {
			if(class_exists($className)) {
				$inst->instances[$key] = new $className;
			} else {
				trigger_error('cn not instantiate ' . $className . ' class does not exist', \E_USER_ERROR);
			}
		}
		return $inst->instances[$key];
	}

	private function sessionIsValid(Session\DomainConfig $config)
	{
		// expiry
		$phpIniLifeTime = ini_get('session.cookie_lifetime');
		if ($phpIniLifeTime == 0) {
			$lifeTimeValid = true;
		} else {
			// should we resend the cookie or sth. when the session is almost expiring?
			$lifeTimeValid = $this->startTime + $phpIniLifeTime > $phpIniLifeTime;
		}
		// client
		if ($config->checkClient) {
			$clientValid = $this->verifyClient();
			if (!$clientValid) {
				trigger_error('invalid client intercepted !!! expected ' . $this->clientHash . ' actual ' . self::getClientHash(), E_USER_WARNING);
			}
		} else {
			$clientValid = true;
		}
		return $lifeTimeValid && $clientValid;
		;
	}

	private function verifyClient()
	{
		return $this->clientHash == self::getClientHash();
	}

	private static function getClientHash()
	{
		$hash = '';
		foreach (array('REMOTE_ADDR', 'HTTP_USER_AGENT') as $prop) {
			$hash .= ! empty($_SERVER[$prop]) ? $_SERVER[$prop] : 'empty-' . $prop;
		}
		return $hash;
	}

	/**
	 * get my self
	 *
	 * @return Foomo\Session
	 *
	 */
	public static function getInstance()
	{
		if (self::$instance) {
			return self::$instance;
		} else {
			if (!isset($_SESSION[__CLASS__])) {
				trigger_error('you must set up your session before you can use it', E_USER_ERROR);
			}
			return $_SESSION[__CLASS__];
		}
	}

	/**
	 * check configuration (and set up the session)
	 *
	 */
	private static $id;

	/**
	 * session config for the core
	 * 
	 * @return Foomo\Session\DomainConfig
	 */
	public static function getConf()
	{
		return Config::getConf(\Foomo\Module::NAME, Session\DomainConfig::NAME);
	}

	public static function init()
	{
		self::$id = 'disabled'; // rand(0,10000000000000000);
		if (php_sapi_name() == 'cli') {
			self::disable();
		}
		/* @var $conf \Foomo\Session\DomainConfig */
		$conf = self::getConf();
		//var_dump($conf);
		if (!is_null($conf) && $conf->enabled) {
			switch ($conf->type) {
				case'foomo':
					register_shutdown_function(array(__CLASS__, 'foomoSessionShutDown'));
					if (!isset($_COOKIE[$conf->name])) {
						self::startFoomoSession($conf);
					} else {
						self::$instance = self::foomoLoad($_COOKIE[$conf->name]);
						if (!self::$instance || !self::$instance->sessionIsValid($conf)) {
							if (!self::$instance) {
								if (!self::$disabled) {
									// trigger_error('no inst from >' . $_COOKIE[$conf->name] . '<');
								}
							}
							if (self::$instance && !self::$instance->sessionIsValid($conf)) {
								if (!self::$disabled) {
									trigger_error('invalid session');
								}
							}
							self::startFoomoSession($conf);
						} else {
							// trigger_error('     >>>>>>>>>>> session woke up with ' . self::$instance->sessionId);
							self::$instance->dirty = false;

							if (false && $conf->cookieLifetimeThreshold > 0) {
								$lifetime = ini_get('session.cookie_lifetime');
								$diff = (self::$instance->startTime + $lifetime) - time();
								if ($diff <= (integer) $conf->cookieLifetimeThreshold) {
									trigger_error('------- extending session - for ' . $_COOKIE[$conf->name]);
									// reload first
									self::lockAndLoad();
									self::sendCookie($conf, $_COOKIE[$conf->name]);
									self::$instance->startTime = time();
								}
							}
						}
					}
					break;
				default:
					session_name($conf->name);
					session_start();
					if (!isset($_SESSION[__CLASS__])) {
						self::$instance = $_SESSION[__CLASS__] = new self();
						self::$instance->type = 'php';
						self::$instance->dirty = true;
						self::$instance->sessionId = session_id();
					} else {
						self::$instance = $_SESSION[__CLASS__];
					}
			}
			if (function_exists('apache_setenv')) {
				apache_setenv('FOOMO_SESSION_ID', self::$instance->sessionId);
			}
		} else {
			self::$disabled = true;
		}
	}

	private static function startFoomoSession(Session\DomainConfig $conf)
	{
		self::$instance = new self;
		self::$instance->dirty = true;
		self::$instance->type = $conf->type;
		self::$instance->sessionId = self::foomoMakeSessionId($conf->salt, $conf->paranoiaLevel);
		self::$instance->foomoLock();
		self::sendCookie($conf, self::$instance->sessionId);
	}

	private static function sendCookie(Session\DomainConfig $conf, $sessionId)
	{

		if (is_null($sessionId)) {
			$sessionId = self::foomoMakeSessionId($conf->salt, $conf->paranoiaLevel);
		}
		$secure = false;
		$sendCookie = true;
		if (ini_get('session.cookie_secure')) {
			$secure = true;
			if (!isset($_SERVER['HTTPS'])) {
				$sendCookie = false;
			}
		}
		if ($sendCookie && !self::$disabled) {
			$lifetime = ini_get('session.cookie_lifetime');
			$expire = ($lifetime == 0) ? 0 : time() + $lifetime;
			setcookie($conf->name, $sessionId, $expire, ini_get('session.cookie_path'), ini_get('session.cookie_domain'), $secure); //, ini_get('session.cookie_httponly'));
		}
		return $sessionId;
	}

	/**
	 * paranoid session id
	 *
	 * @param string $salt salt it
	 *
	 * @return string
	 */
	public static function foomoMakeSessionId($salt='nosaltgiven', $paranoiaLevel = 1000)
	{
		$one = microtime(); //uniqid(null, true);
		$three = '';
		for ($i = 0; $i < rand(100, 1000); $i++) {
			$three .= rand(0, 10);
		}
		$two = microtime();
		$sessionId = sha1($salt . $one . $two . $three);
		if (!file_exists(self::foomoGetFileName($sessionId))) {
			return $sessionId;
		} else {
			// will this ever be called ?! ;)
			return self::foomoMakeSessionId($salt, $paranoiaLevel);
		}
	}

	private static $fp;

	private function foomoLock()
	{
		if (!self::$disabled) {
			$lockFile = self::foomoGetFileName($this->sessionId);
			self::$fp = fopen($lockFile, 'w');

			if (!flock(self::$fp, LOCK_EX)) {
				trigger_error('--- no write lock ---' . self::$id);
			}
		}
	}

	private function foomoSave()
	{
		if (self::$fp) {
			file_put_contents(self::foomoGetContentsFileName($this->sessionId), serialize($this));
		} else {
			if (!self::$disabled) {
				trigger_error('how to write, if the is no fp', E_USER_ERROR);
			}
		}
	}

	private static function foomoLoad($sessionId, $reload = false)
	{
		$fileName = self::foomoGetFileName($sessionId);
		$contentFileName = self::foomoGetContentsFileName($sessionId);
		if (file_exists($fileName)) {
			if (!self::$fp) {
				$fp = fopen($fileName, 'w');
				//trigger_error('--- locking read ---' . self::$id);
				if (!flock($fp, LOCK_EX)) {
					//trigger_error('--- locked read ---' . self::$id);
					trigger_error('--- lock read failed !!! ---' . self::$id, E_USER_ERROR);
				}
				$unserialized = self::loadSessionFromFs($contentFileName);
				fclose($fp);
				//trigger_error('--- released read ---' . self::$id);
			} else {
				$unserialized = self::loadSessionFromFs($contentFileName);
			}
			if (!$unserialized && !$reload) {
				if (!self::$disabled) {
					trigger_error('--- ' . self::$id . ' WTF session file is empty ' . strlen($fileContents), E_USER_ERROR);
				}
			}
			return $unserialized;
		} else {
			if (!self::$disabled) {
				// trigger_error('no session file ....' . $fileName);
			}
			return false;
		}
	}

	private static function loadSessionFromFs($contentFileName)
	{
		if (file_exists($contentFileName)) {
			$fileContents = file_get_contents($contentFileName);
			if (strlen($fileContents) > 0) {
				return unserialize($fileContents);
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	public static function foomoGetFileName($sessionId)
	{
		$fileName = ini_get('session.save_path') . DIRECTORY_SEPARATOR . self::PREFIX . '-' . $sessionId;
		return $fileName;
	}

	public static function foomoGetContentsFileName($sessionId)
	{
		return self::foomoGetFileName($sessionId) . '-' . self::CONTENTS_POSTFIX;
	}

	public static function lockAndLoadIfEnabled($sessionId = null)
	{
		$conf = self::getConf();
		if ($conf && self::getConf()->enabled) {
			self::lockAndLoad($sessionId);
		}
	}

	/**
	 * lock and load the session - you have to do this *every* time you write
	 * into the session
	 *
	 * do not forget to call saveAndRelease, as soon as the session interaction is done
	 *
	 * @param string $sessionId load another session, take care this only means looking into it, not hijacking it ...
	 *
	 */
	public static function lockAndLoad($sessionId = null)
	{
		if (!self::$disabled) {
			if (self::$instance && self::$instance->type == 'foomo') {
				if ($sessionId && self::$fp && $sessionId != self::$instance->sessionId) {
					self::saveAndRelease();
				}
				if (!self::$fp) {
					self::$instance->foomoLock();
					if ($sessionId) {
						// force a sessionId
						$reloaded = self::foomoLoad($sessionId, true);
					} else {
						$reloaded = self::foomoLoad(self::$instance->sessionId, true);
					}

					if ($reloaded) {
						self::$instance = $reloaded;
					}
					self::$instance->dirty = true;
				}
			} else {
				if (!self::$instance) {
					trigger_error('can not lock session, that was not started', E_USER_ERROR);
				}
			}
		}
	}

	public static function destroy($reinit = true)
	{
		$inst = self::getInstance();
		self::release();

		$files = array(
			self::foomoGetFileName($inst->sessionId),
			self::foomoGetContentsFileName($inst->sessionId)
		);
		foreach ($files as $file) {
			if (file_exists($file)) {
				unlink($file);
			}
		}
		self::$instance = null;
		if ($reinit) {
			self::init();
		}
	}

	/**
	 * tells you how many times the session was written
	 *
	 * @return integer
	 */
	public static function getAge()
	{
		if (self::$instance) {
			return self::$instance->age;
		}
	}

	public static function getSessionIdIfEnabled()
	{
		if (self::getEnabled()) {
			return self::getSessionId();
		} else {
			return 'disabled';
		}
	}

	public static function getEnabled()
	{
		$conf = self::getConf();
		if ($conf) {
			return self::getConf()->enabled;
		} else {
			return false;
		}
	}

	/**
	 * get the current session id
	 *
	 * @return string
	 */
	public static function getSessionId()
	{
		if (self::$instance) {
			return self::$instance->sessionId;
		} else {
			trigger_error('trying to get a session id, before the session was initialized', E_USER_WARNING);
		}
	}

	private static function release()
	{
		if (self::$fp) {
			fclose(self::$fp);
			self::$fp = null;
		}
	}

	private static $disabled = false;

	public static function disable()
	{
		self::$disabled = true;
		self::release();
	}

	/**
	 * save and release the session - this can be very helpful to prevent
	 * session lock downs
	 *
	 * remember you can always reload the session with lockAndLoad
	 */
	public static function saveAndRelease()
	{
		if (self::$fp) {
			self::$instance->foomoSave();
			self::release();
		}
	}

	public static function foomoSessionShutDown()
	{
		if (!self::$disabled && self::$instance && self::$instance->dirty) {
			self::$instance->dirty = false;
			self::saveAndRelease();
		}
	}

}
