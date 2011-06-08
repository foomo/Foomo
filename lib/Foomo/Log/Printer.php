<?php

/*
 * bestbytes-copyright-placeholder
 */

namespace Foomo\Log;

class Printer {

	private $errorMap;

	public function __construct()
	{
		$this->errorMap = array();
		foreach (array('USER_', '', 'CORE_') as $scope) {
			foreach (array('WARNING', 'NOTICE', 'ERROR', 'DEPRECATED', 'STRICT') as $level) {
				$constName = 'E_' . $scope . $level;
				if (defined($constName)) {
					$this->errorMap[\constant($constName)] = $constName;
				}
			}
		}
	}

	public function printSeparator($counter)
	{
		return '------------------------------- ' . $counter . ' -------------------------------' . PHP_EOL;
	}

	public function printEntry(Entry $entry)
	{
		return \Foomo\Module::getView($this, 'entry', array('entry' => $entry, 'printer' => $this));
	}

	public function printSession(UserSession $session)
	{
		return \Foomo\Module::getView($this, 'session', array('session' => $session, 'printer' => $this));
	}

	public function phpErrorIntToString($int)
	{
		if (isset($this->errorMap[$int])) {
			return $this->errorMap[$int];
		} else {
			return $int;
		}
	}

}