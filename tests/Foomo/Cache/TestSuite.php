<?php

namespace Foomo\Cache;

use Foomo\TestRunner\Suite;

class TestSuite extends Suite {
	/**
	 * get a list of class name, which will be accumulated into a test as a suite
	 *
	 * @return array
	 */
	public function foomoTestSuiteGetList()
	{
 		return array(
			'Foomo\\Cache\\ManagerTest',
			'Foomo\\Cache\\ProxyTest',
			'Foomo\\Cache\\Persistence\\ExprTest',
			'Foomo\\Cache\\InvalidatorTest',
			'Foomo\\Cache\\DependencyModelTest',
			'Foomo\\Cache\\Persistence\\Fast\\APCTest',
			'Foomo\\Cache\\Persistence\\Fast\\MemcacheTest',
			'Foomo\\Cache\\Persistence\\Queryable\\PDOPersistorTest',
			'Foomo\\Cache\\Persistence\\Queryable\\PDOExprTest',
            'Foomo\\Cache\\Persistence\\Queryable\\MongoPersistorTest',
        );
	}
}