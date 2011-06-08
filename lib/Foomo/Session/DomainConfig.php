<?php

/*
 * bestbytes-copyright-placeholder
 */

namespace Foomo\Session;

use Foomo\Session;
use Foomo\Config\AbstractConfig;

/**
 * set up how your session is supposed to work
 */
class DomainConfig extends AbstractConfig {
	const NAME = 'Foomo.session';

	const DEFAULT_NAME = 'foomoSession';
	const TYPE_FOOMO = 'foomo';
	const TYPE_PHP = 'php';

	/**
	 * enable the session or not
	 *
	 * @var string
	 */
	public $enabled = true;
	/**
	 * name of the session
	 *
	 * @var string
	 */
	public $name = self::DEFAULT_NAME;
	/**
	 * php | foomo
	 * 
	 * @var string
	 */
	public $type = self::TYPE_FOOMO; // 'foomo';
	/**
	 * hoch paranoid should it be 100 - 10000
	 * 
	 * @var integer
	 */
	public $paranoiaLevel = 500;
	/**
	 * salt
	 *
	 * @var string
	 */
	public $salt = '';
	/**
	 *
	 * @var boolean
	 */
	public $checkClient = false;
	/**
	 * update the cookie expiration with every call - not the default php behaviour and unknown in respects to performance and browsers
	 * 
	 * @var boolean
	 */
	public $cookieLifetimeThreshold = 0;

	public function __construct($createDefault = false)
	{
		if ($createDefault) {
			$this->salt = Session::foomoMakeSessionId(md5(serialize($_SERVER) . uniqid()), rand(10000, 30000));
			$this->paranoiaLevel = rand(100, 1000);
		}
	}

}