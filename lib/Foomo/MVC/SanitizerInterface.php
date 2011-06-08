<?php

namespace Foomo\MVC;

/**
 * sanitized user input
 */
interface SanitizerInterface {
	/**
	 * @param mixed $unsanitzedValue unsanitized value
	 */
	public function __construct($unsanitzedValue);
	/**
	 * get the sanitized value
	 * 
	 * @return mixed
	 */
	public function getSanitizedValue();
}