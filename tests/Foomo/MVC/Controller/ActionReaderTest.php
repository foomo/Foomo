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

namespace Foomo\MVC\Controller;

/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
class ActionReaderTest extends \PHPUnit_Framework_TestCase {
	const MOCK_CONTROLLER_CLASS = 'Foomo\\MVC\\Mock\\Frontend\\Controller';
	const MOCK_ACTION_CONTROLLER_FOO = 'Foomo\\MVC\\Mock\\Frontend\\Controller\\ActionFoo';
	public function testSearchControllerActionClasses()
	{
		$expected = array(
			'Foomo\\MVC\\Mock\\Frontend\\Controller\\ActionFoo'
		);
		$actual = ActionReader::searchControllerActionClasses(self::MOCK_CONTROLLER_CLASS);
		sort($actual);
		sort($expected);
		$this->assertEquals($expected, $actual, 'class list did not match');
	}

	public function testCachedGetClassActions()
	{
		$actions = ActionReader::cachedGetClassActions(self::MOCK_CONTROLLER_CLASS);
		$this->assertCount(2, $actions->actions);
		$def = $actions->actions['default'];
		$foo = $actions->actions['foo'];
		$this->assertInstanceOf('Foomo\\MVC\\Controller\\ClassActions', $actions);
		$this->assertEquals('default', $def->actionNameShort);
		$this->assertEquals(self::MOCK_CONTROLLER_CLASS, $def->controllerName);
		$this->assertEquals('foo', $foo->actionNameShort);
		$this->assertEquals(self::MOCK_ACTION_CONTROLLER_FOO, $foo->controllerName);
	}
}