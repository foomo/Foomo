<?php

/*
 * bestbytes-copyright-placeholder
 */

namespace Foomo\Log;

class Suite extends \Foomo\TestRunner\Suite {
	public function foomoTestSuiteGetList()
	{
		return array(
			__NAMESPACE__ . '\\UtilsTest',
			__NAMESPACE__ . '\\ReaderTest'
		);
	}
}