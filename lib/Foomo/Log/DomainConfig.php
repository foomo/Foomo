<?php

/*
 * bestbytes-copyright-placeholder
 */

namespace Foomo\Log;

use Foomo\Config\AbstractConfig;

/**
 * logger configuration
 */
class DomainConfig extends AbstractConfig {
	const NAME = 'Foomo.logger';
	public $enabled = false;
	public $replacePhpErrorLog = false;
	public $stringLogLength = 64;
	public $logPostVars = false;
	public $logGetVars = false;
	public $trackServerVars = array('HTTP_USER_AGENT', 'HTTP_ACCEPT_LANGUAGE', 'REMOTE_ADDR', 'SERVER_PROTOCOL', 'QUERY_STRING', 'SCRIPT_NAME', 'HTTPS');
	public $trackErrors = array('E_DEPRECATED', 'E_USER_DEPRECATED', 'E_PARSE', 'E_USER_WARNING', 'E_USER_ERROR', 'E_CORE_WARNING', 'E_CORE_ERROR', 'E_WARNING', 'E_ERROR', 'E_COMPILE_WARNING', 'E_COMPILE_ERROR', 'E_RECOVERABLE_ERROR');
	public $trackStrackTracesForErrors = array('E_USER_WARNING', 'E_USER_ERROR', 'E_CORE_WARNING', 'E_CORE_ERROR', 'E_WARNING', 'E_ERROR', 'E_COMPILE_WARNING', 'E_COMPILE_ERROR', 'E_RECOVERABLE_ERROR');
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