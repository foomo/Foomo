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

namespace Foomo\Log\Filters;

/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
class CommonFilters extends AbstractFilterProvider {

	/**
	 * only my session
	 */
	public static function mySession(\Foomo\Log\Entry $entry)
	{
		if ($entry->sessionId == \Foomo\Session::getSessionId()) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * all errors and exceptions
	 */
	public static function allBadThings(\Foomo\Log\Entry $entry)
	{
		if (count($entry->phpErrors) > 0 || $entry->exception) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * exceptions only
	 */
	public static function onlyException(\Foomo\Log\Entry $entry)
	{
		if ($entry->exception) {
			return true;
		} else {
			return false;
		}
	}

}