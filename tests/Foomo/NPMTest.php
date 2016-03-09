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

/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
class NPMTest extends \PHPUnit_Framework_TestCase {
	private static function getMockJSON($name)
	{
		return file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'npm' . DIRECTORY_SEPARATOR . $name . '.json');
	}

	public function testReadPackages()
	{
		$listJSON = self::getMockJSON('packagesList');
		$packages = NPM::readPackages($listJSON);
		$this->assertNotEmpty($packages);
		foreach($packages as $package) {
			$this->assertInstanceOf('Foomo\\Modules\\Resource\\NPMPackage', $package);
			foreach(['name', 'version', 'description'] as $prop) {
				$this->assertNotEmpty($package->{$prop});
			}
		}
	}
}