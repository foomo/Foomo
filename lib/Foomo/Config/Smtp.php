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

namespace Foomo\Config;

/**
 * smtp configuration
 *
 * @link    www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author  jan <jan@bestbytes.de>
 */
class Smtp extends AbstractConfig
{
	// --------------------------------------------------------------------------------------------
	// ~ Constants
	// --------------------------------------------------------------------------------------------

	const NAME = 'Foomo.smtp';

	// --------------------------------------------------------------------------------------------
	// ~ Variables
	// --------------------------------------------------------------------------------------------

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
	 * optionally uses email to use i.e. as From
	 *
	 * @var string
	 */
	public $email = '';

	// --------------------------------------------------------------------------------------------
	// ~ Public methods
	// --------------------------------------------------------------------------------------------

	/**
	 * extracts a configuration array, that is compatible with PEAR¬¨¬•s Mail::factory
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
