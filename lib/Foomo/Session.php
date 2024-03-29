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

namespace Foomo;

/**
 * session that provides the ability to run a (non) blocking session
 *
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
class Session
{
	private static $initCalled = false;
	//---------------------------------------------------------------------------------------------
	// ~ Constants
	//---------------------------------------------------------------------------------------------

	const DEAULT_IDENTIFIER = 'defaultInstance';

	//---------------------------------------------------------------------------------------------
	// ~ Variables
	//---------------------------------------------------------------------------------------------

	/**
	 * keeps all the class instances
	 *
	 * @var array
	 */
	private $instances = array();
	/**
	 * singleton
	 *
	 * @var \Foomo\Session
	 */
	private static $instance;
	/**
	 * @var \Foomo\Session\PersistorInterface
	 * @internal
	 */
	public static $persistor;
	/**
	 * my session id
	 *
	 * @var string
	 */
	private $sessionId;
	/**
	 * was the session already locked
	 *
	 * @var bool
	 */
	private $locked = false;
	/**
	 * hash of client information
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
	/**
	 * @var int
	 */
	private $age = 0;
	/**
	 * session is dirty and needs to be written
	 *
	 * @var string
	 */
	private $dirty = false;

	//---------------------------------------------------------------------------------------------
	// ~ Constructor
	//---------------------------------------------------------------------------------------------

	private function __construct()
	{
		$this->clientHash = self::getClientHash();
		$this->startTime = time();
	}

	//---------------------------------------------------------------------------------------------
	// ~ Magic methods
	//---------------------------------------------------------------------------------------------

	public function __wakeUp()
	{
		if (is_null($this->age)) $this->age = 0;
		$this->age++;
	}

	//---------------------------------------------------------------------------------------------
	// ~ Public static methods
	//---------------------------------------------------------------------------------------------

	/**
	 * get a session persistent instance of a class - singleton style
	 *
	 * @param string $className
	 * @param string $identifier multiton
	 *
	 * @return object
	 */
	public static function getClassInstance($className, $identifier = 'defaultInstance')
	{
		$inst = self::getInstance();
		$key = $inst->getInstanceKey($className, $identifier);

		# validate class
		if(!class_exists($className)) {
			trigger_error('can not instantiate ' . $className . ' class does not exist', \E_USER_ERROR);
		} else {
			if (!isset($inst->instances[$key])) {
				self::lockAndLoad();
				self::setClassInstance(new $className, $identifier);
                $inst = self::getInstance();
			}
			return $inst->instances[$key];
			// would be much nicer, but as long, as there is not a magic __get_class method we can not use this ...
			// this does not work with i.e. casted method parameters or get_class(), which we use quite often
			// return ($inst->locked) ? $inst->instances[$key] : new \Foomo\Session\ImmutableProxy($inst->instances[$key]);
		}

	}
	/**
	 * set a session class instance, session must be locked
	 *
	 * @param object $instance
	 * @param string $identifier
	 *
	 * @throws \InvalidArgumentException
	 */
	public static function setClassInstance($instance, $identifier = 'defaultInstance')
	{
		$inst = self::getInstance();
		$inst->checkIsLocked();
		if (!is_object($instance)) {
			throw new \InvalidArgumentException('$instance has to be an object');
		} else {
			$inst->instances[$inst->getInstanceKey(get_class($instance), $identifier)] = $instance;
		}
	}
	/**
	 * remove a class instance from the session
	 *
	 * @param mixed $instOrClassName
	 * @param string $identifier
	 */
	public static function unsetClassInstance($instOrClassName, $identifier = 'defaultInstance')
	{
		$inst = self::getInstance();
		$inst->checkIsLocked();
		if(is_object($instOrClassName)) {
			$className = get_class($instOrClassName);
		} else {
			$className = $instOrClassName;
		}
		$key = $inst->getInstanceKey($className, $identifier);
		if(isset($inst->instances[$key])) {
			unset($inst->instances[$key]);
		}
	}
	/**
	 * is a class instance set
	 *
	 * @param string $className
	 * @param string $identifier
	 *
	 * @return boolean
	 */
	public static function classInstanceIsset($className, $identifier = 'defaultInstance')
	{
		$inst = self::getInstance();
		return isset($inst->instances[$inst->getInstanceKey($className, $identifier)]);
	}
	private function getInstanceKey($className, $identifier)
	{
		return $className . $identifier;
	}
	private function sessionIsValid(Session\DomainConfig $config)
	{
		// expiry
		$phpIniLifeTime = (int) ini_get('session.cookie_lifetime');
		if ($phpIniLifeTime == 0) {
			$lifeTimeValid = true;
		} else {
			// should we resend the cookie or sth. when the session is almost expiring?
			$lifeTimeValid = (($this->startTime + $phpIniLifeTime) > $phpIniLifeTime);
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
	}
	private function checkIsLocked()
	{
		if (!$this->locked) throw new \Exception('write access to an unlocked session');
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
	 * @return \Foomo\Session
	 *
	 */
	public static function getInstance()
	{
		if (!self::$instance) {
			trigger_error('can not access session - is it enabled?', E_USER_ERROR);
		}
		return self::$instance;
	}
	/**
	 * session config for the core
	 *
	 * @return \Foomo\Session\DomainConfig
	 */
	public static function getConf()
	{
		return Config::getConf(Module::NAME, Session\DomainConfig::NAME);
	}

	public static function init($reStart = false)
	{
		self::$initCalled = true;
		/* @var $conf \Foomo\Session\DomainConfig */
		$conf = self::getConf();
		if (!is_null($conf) && $conf->enabled) {
			$persistorClass = 'Foomo\\Session\\Persistence\\' . $conf->persistor;
			if(!class_exists($persistorClass)) {
				trigger_error('invalid persistor', E_USER_WARNING);
				self::$persistor = new Session\Persistence\FS();
			} else {
				self::$persistor = new $persistorClass;
			}
			register_shutdown_function(array(__CLASS__, 'foomoSessionShutDown'));
			if (!isset($_COOKIE[$conf->name]) || $reStart) {
				// no cookie
				self::start($conf);
			} else {
				// got a cookie
				self::$instance = self::$persistor->load($_COOKIE[$conf->name]);
				if (!self::$instance || !self::$instance->sessionIsValid($conf)) {
					if (self::$instance && !self::$instance->sessionIsValid($conf)) {
						if (!self::$disabled) trigger_error('invalid session');
					}
					self::start($conf);
				} else {
					self::$instance->dirty = false;
					if (false && $conf->cookieLifetimeThreshold > 0) {
						$lifetime = (int) ini_get('session.cookie_lifetime');
						$diff = self::$instance->startTime + $lifetime - time();
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
			if (function_exists('apache_setenv')) {
				apache_setenv('FOOMO_SESSION_ID', self::$instance->sessionId);
			}
		} else {
			self::$disabled = true;
		}
	}

	private static function start(Session\DomainConfig $conf)
	{
		self::$instance = new self;
		self::$instance->dirty = true;
		self::$instance->sessionId = self::makeSessionId($conf->salt, $conf->paranoiaLevel);
		self::$instance->lock();
		self::sendCookie($conf, self::$instance->sessionId);
	}

	private static function sendCookie(Session\DomainConfig $conf, $sessionId)
	{

		if (is_null($sessionId)) {
			$sessionId = self::makeSessionId($conf->salt, $conf->paranoiaLevel);
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
			$lifetime = (int) ini_get('session.cookie_lifetime');
			$expire = ($lifetime == 0) ? 0 : time() + $lifetime;
			setcookie($conf->name, $sessionId, $expire, ini_get('session.cookie_path'), ini_get('session.cookie_domain'), $secure, ini_get('session.cookie_httponly'));
		}
		return $sessionId;
	}

	/**
	 * paranoid session id
	 *
	 * @param string $salt salt it
	 * @param integer $paranoiaLevel
	 *
	 * @return string
	 */
	public static function makeSessionId($salt='nosaltgiven', $paranoiaLevel = 1000)
	{
		$one = microtime();
		$three = '';
		for ($i = 0; $i < rand(100, 1000); $i++) {
			$three .= rand(0, 10);
		}
		$two = microtime();
		$sessionId = sha1($salt . $one . $two . $three);
		if (!self::$persistor->exists($sessionId)) {
			return $sessionId;
		} else {
			// will this ever be called ?! ;)
			// well in theory yess
			return self::makeSessionId($salt, $paranoiaLevel);
		}
	}

	private function lock()
	{
		if (!self::$disabled) {
			self::$persistor->lock($this->sessionId);
		}
	}
	/**
	 * shortcut to check if session is enabled and in that case lockAndLoad()
	 *
	 * @param string $sessionId
	 */
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
	public static function lockAndLoad($sessionId=null)
	{
		if (!self::$disabled) {
			if (self::$instance && !self::$instance->locked) {
				if (is_null($sessionId)) {
					$sessionId = self::getSessionId();
				}
				self::$persistor->lock($sessionId);
				$inst = self::$persistor->load($sessionId);
				if(!is_null($inst)) {
					self::$instance = $inst;
				}
				self::$instance->locked = self::$instance->dirty = true;
			} else if (!self::$instance) {
				trigger_error('can not lock session, that was not started', E_USER_ERROR);
			}
		}
	}
	/**
	 * destroy the current session will destroy the current session data
	 *
	 * @param boolean $reInit by default will restart with the current session id
	 */
	public static function destroy($reInit = true)
	{
		$inst = self::getInstance();
		self::$persistor->release($inst->sessionId);
		self::$persistor->destroy($inst->sessionId);
		self::$instance = null;
		if ($reInit) {
			self::init($reInit);
		} else {
			self::disable();
		}
	}

	/**
	 * tells you how many times the session was written
	 *
	 * @return integer|null
	 */
	public static function getAge()
	{
		return self::$instance?self::$instance->age:null;
	}
	/**
	 * get the sessionId, if the session is enabled
	 *
	 * @return string|null
	 */
	public static function getSessionIdIfEnabled()
	{
		return self::getEnabled()?self::getSessionId():null;
	}
	/**
	 * is the session enabled
	 *
	 * @return boolean
	 */
	public static function getEnabled()
	{
		if(self::$initCalled) {
			$conf = self::getConf();
			if ($conf) {
				return $conf->enabled && !self::$disabled;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * get the current session id
	 *
	 * @return string|null
	 */
	public static function getSessionId()
	{
		if (self::$instance) {
			return self::$instance->sessionId;
		} else {
			trigger_error('trying to get a session id, before the session was initialized', E_USER_WARNING);
			return null;
		}
	}

	private static $disabled = false;
	/**
	 * disable the session
	 */
	public static function disable()
	{
		self::$disabled = true;
		if(self::$persistor) {
			self::$persistor->release(self::$instance->sessionId);
		}
	}

	/**
	 * save and release the session - this can be very helpful to prevent
	 * session lock downs
	 *
	 * remember you can always reload the session with lockAndLoad
	 */
	public static function saveAndRelease()
	{
		if (self::$instance && self::$instance->locked) {
			self::$instance->locked = false;
			self::$persistor->persist(
				self::$instance->sessionId,
				self::$instance
			);
			self::$persistor->release(self::$instance->sessionId);
		}
	}
	/**
	 * registered as a shutdown function
	 *
	 * @internal
	 */
	public static function foomoSessionShutDown()
	{
		if (!self::$disabled && self::$instance && self::$instance->dirty) {
			if(!self::$instance->locked) {
				self::lockAndLoad();
			}
			self::$instance->dirty = false;
			self::saveAndRelease();
		}
	}
	/**
	 * @internal
	 * @return array
	 */
	public static function getSessionInstances()
	{
		return self::$instance->instances;
	}
}
