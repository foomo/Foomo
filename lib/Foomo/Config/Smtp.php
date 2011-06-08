<?php

/*
 * bestbytes-copyright-placeholder
 */

namespace Foomo\Config;

/**
 * smtp configuration
 */
class Smtp extends AbstractConfig {
	const NAME = 'Foomo.smtp';
	/**
	 * hostname ore IP address of the smtp server
	 *
	 * @var string
	 */
	public $host = null;
	/**
	 * port to make the connection on
	 *
	 * @var integer
	 */
	public $port = 25;
	/**
	 * user name for the mail account
	 *
	 * @var string
	 */
	public $username = '';
	/**
	 * password of the smtp account
	 *
	 * @var string
	 */
	public $password = '';

	/**
	 * extracts a configuration array, that is compatible with PEAR¬¥s Mail::factory
	 *
	 * @internal
	 *
	 * @return array
	 */
	public function toPearMailerFactoryArray()
	{
		$ret = array();
		foreach ($this as $key => $value) {
			$ret[$key] = $value;
		}
		if (!empty($this->username) && !empty($this->password)) {
			$ret['auth'] = true;
		}
		return $ret;
	}

}
