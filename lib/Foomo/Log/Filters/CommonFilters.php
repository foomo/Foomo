<?php

/*
 * bestbytes-copyright-placeholder
 */

namespace Foomo\Log\Filters;

/**
 * common log filters
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