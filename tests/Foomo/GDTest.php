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
	const MAX_SIZE = 60;
	public function setUp()
	{
		if(!function_exists('gd_info')) {
			$this->skipTest('no gd installed');
			//$this->markTestSkipped('gd is not installed');
		}
	}
	private function maxResize($img, $max)
	{
		$src = __DIR__ . \DIRECTORY_SEPARATOR . 'gdResources' . \DIRECTORY_SEPARATOR . $img . '.png';
		$target = tempnam(Config::getTempDir(), 'GDTest-');
		GD::resampleImageToMaxValues('image/png', 'image/png', $src, $target, $max, $max);
		$s = getimagesize($target);
		unlink($target);
		$size = (object) array('width' => $s[0], 'height' => $s[1]);
		return $size;
	}
	public function testMaxResizeLandscape()
	{
		$resized = $this->maxResize('landscape', self::MAX_SIZE);
		$this->assertEquals(self::MAX_SIZE, $resized->width);
		$this->assertTrue(self::MAX_SIZE >= $resized->height);
	}
	public function testMaxResizeSquare()
	{
		$size = $this->maxResize('square', self::MAX_SIZE);
		$this->assertEquals(self::MAX_SIZE, $size->width);
		$this->assertEquals(self::MAX_SIZE, $size->height);
	}
	public function testMaxResizePortrait()
	{
		$resized = $this->maxResize('portrait', self::MAX_SIZE);
		$this->assertEquals(self::MAX_SIZE, $resized->height);
		$this->assertTrue(self::MAX_SIZE >= $resized->width);
    }
}