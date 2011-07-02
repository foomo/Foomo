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

namespace Foomo\Log;

use Foomo\Config\AbstractConfig;

/**
 * logger configuration
 * 
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
class DomainConfig extends AbstractConfig {
	const NAME = 'Foomo.logger';
	/**
	 * @var boolean
	 */
	public $enabled = false;
	/**
	 * @var boolean
	 */
	public $replacePhpErrorLog = false;
	/**
	 * how many characters will be displayed of args, when printing errors
	 * 
	 * @var integer
	 */
	public $stringLogLength = 64;
	/**
	 * @var boolean
	 */
	public $logPostVars = false;
	/**
	 * @var boolean
	 */
	public $logGetVars = false;
	/**
	 * what $_SERVER vars should be logged
	 * 
	 * @var string[]
	 */
	public $trackServerVars = array('HTTP_USER_AGENT', 'HTTP_ACCEPT_LANGUAGE', 'REMOTE_ADDR', 'SERVER_PROTOCOL', 'QUERY_STRING', 'SCRIPT_NAME', 'HTTPS');
	/**
	 * what error types to track
	 * 
	 * @var string[]
	 */
	public $trackErrors = array('E_DEPRECATED', 'E_USER_DEPRECATED', 'E_PARSE', 'E_USER_WARNING', 'E_USER_ERROR', 'E_CORE_WARNING', 'E_CORE_ERROR', 'E_WARNING', 'E_ERROR', 'E_COMPILE_WARNING', 'E_COMPILE_ERROR', 'E_RECOVERABLE_ERROR');
	/**
	 * what error typed should a stack trace be recorded for
	 * 
	 * @var string[]
	 */
	public $trackStrackTracesForErrors = array('E_USER_WARNING', 'E_USER_ERROR', 'E_CORE_WARNING', 'E_CORE_ERROR', 'E_WARNING', 'E_ERROR', 'E_COMPILE_WARNING', 'E_COMPILE_ERROR', 'E_RECOVERABLE_ERROR');
	/**
	 * @var boolean
	 */
	public $trackExceptions = true;
	public $trackErrorsAsIntegers = array();
	public $trackStrackTracesForErrorsAsIntegers = array();

	public function getValue()
	{
		$ret = parent::getValue();
		unset($ret['trackStrackTracesForErrorsAsIntegers']);
		unset($ret['trackErrorsAsIntegers']);
		return $ret;
	}

	public function setValue($value)
	{
		parent::setValue($value);

		// filter invalid constants
		foreach(array('trackErrors', 'trackStrackTracesForErrors') as $constArrayProp) {
			$filtered = array();
			foreach ($this->$constArrayProp as $constName) {
				$constName = trim($constName);
				if(defined($constName)) {
					$filtered[] = $constName;
				} else {
					trigger_error('unknown constant ' . $constName, E_USER_WARNING);
				}
			}
			$this->$constArrayProp = $filtered;
		}

		$this->trackErrorsAsIntegers = array();
		foreach ($this->trackErrors as $constName) {
			$this->trackErrorsAsIntegers[] = \constant($constName);
		}
		$this->trackStrackTracesForErrorsAsIntegers = array();
		foreach ($this->trackStrackTracesForErrors as $constName) {
			$this->trackStrackTracesForErrorsAsIntegers[] = \constant($constName);
		}
	}

}