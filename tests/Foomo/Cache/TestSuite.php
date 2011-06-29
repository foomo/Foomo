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