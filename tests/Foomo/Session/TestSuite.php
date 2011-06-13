<?php

namespace Foomo\Session;

use Foomo\TestRunner\Suite;

class TestSuite extends Suite {
	public function foomoTestSuiteGetList()
	{
 		return array(
			__NAMESPACE__ . '\\ImmutableProxyTest',
			'Foomo\\SessionTest'
		);
	}
}