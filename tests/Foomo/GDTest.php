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
class GDTest extends \PHPUnit_Framework_TestCase {
	public function testMaxResize()
	{
        if(function_exists('gd_info')) {
            $src = __DIR__ . \DIRECTORY_SEPARATOR . 'gdResources' . \DIRECTORY_SEPARATOR . 'source.png';
            $target = tempnam(Config::getTempDir(), 'GDTest-');
            GD::resampleImageToMaxValues('image/png', 'image/png', $src, $target, 60, 60);
            $s = getimagesize($target);
            $this->assertEquals(60, $s[1]);
		} else {
			$this->markTestSkipped('gd is not installed');
		}
    }
}