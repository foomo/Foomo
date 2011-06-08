<?php

/*
 * bestbytes-copyright-placeholder
 */

namespace Foomo\Mailer;

/**
 * smtp settings for mails
 * 
 * @deprecated use Foomo\Config\Smtp instead
 */
class SettingsSmtp {

	/**
	 * hostname ore IP address of the smtp server
	 *
	 * @var string
	 */
	public $host;
	/**
	 * user name for the mail account
	 *
	 * @var string
	 */
	public $username;
	/**
	 * password of the smtp account
	 *
	 * @var string
	 */
	public $password;

	/**
	 * dump settings as an array
	 *
	 * @return array
	 */
	public function toArray()
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