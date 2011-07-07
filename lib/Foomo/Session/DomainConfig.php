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
use Foomo\Config\AbstractConfig;

/**
 * set up how your session is supposed to work
 * 
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
class DomainConfig extends AbstractConfig {
	const NAME = 'Foomo.session';
	const DEFAULT_NAME = 'foomoSession';
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
	 * name of the persistor
	 *
	 * @var string
	 */
	public $persistor = 'FS';
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
			$this->salt = 'change this salt';
			$this->paranoiaLevel = rand(100, 1000);
		}
	}
}