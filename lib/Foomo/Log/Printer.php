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

/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
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