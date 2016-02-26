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

namespace Foomo\Config;

/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
class UtilsTest extends \PHPUnit_Framework_TestCase {
	private function clean()
	{

		\Foomo\Config::removeConf(\Foomo\Module::NAME, MockConfig::NAME, 'test');
		Utils::removeOldConfigs(\Foomo\Module::NAME, MockConfig::NAME, 'test');

	}
	public function setUp()
	{
		$this->clean();
	}
	public function tearDown()
	{
		$this->clean();
	}

	private function countOldMocks()
	{
		$oldConfigs = Utils::getOldConfigs();
		$foundOldMocks = 0;
		foreach($oldConfigs as $oldConfig) {
			if($oldConfig->name == MockConfig::NAME && $oldConfig->module == \Foomo\Module::NAME) {
				$foundOldMocks ++;
			}
		}
		return $foundOldMocks;
	}
	public function testOldConfigGC()
	{
		for($i=0;$i<5;$i++) {
			\Foomo\Config::setConf(new MockConfig(), \Foomo\Module::NAME, 'test');
			sleep(2);
		}


		$this->assertEquals(4, $this->countOldMocks(), "there should have been three old mocks");
		echo Utils::oldConfigGC(2);
		$this->assertEquals(2, $this->countOldMocks(), "there should have been three old mocks after cleanup");
	}
}