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

namespace Foomo;

use PHPUnit_Framework_TestCase as TestCase;
use Foomo\Module;

class ModuleTest extends TestCase {
	public function testGetView()
	{
		$view = Module::getView(new Frontend\Model, 'default', array());
		$this->assertTrue($view instanceof View);
	}
	public function testGetTranslation()
	{
		$translation = Module::getTranslation('Foomo\\Frontend', array('en'));
		$this->assertTrue($translation instanceof Translation);
		$this->assertEquals('Hello %s !', $translation->_('GREET_DEFAULT'));
	}
}