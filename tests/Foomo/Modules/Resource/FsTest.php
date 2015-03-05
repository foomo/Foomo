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

namespace Foomo\Modules\Resource;

use PHPUnit_Framework_TestCase as TestCase;

/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
class FsTest extends TestCase {

    private static function getTestfilename()
    {
        return \Foomo\Config::getTempDir(\Foomo\Module::NAME) . DIRECTORY_SEPARATOR . 'fs-resource-test-dir' . DIRECTORY_SEPARATOR . 'bar' . DIRECTORY_SEPARATOR . 'deep' . DIRECTORY_SEPARATOR . 'baz.test';
    }
    private static function cleanup()
    {
        $testFilename = self::getTestfilename();
        if(file_exists($testFilename)) {
            unlink($testFilename);
        }
        $tempdir = \Foomo\Config::getTempDir(\Foomo\Module::NAME);
        $rmDir = dirname($testFilename);
        while($tempdir != $rmDir) {
            if(file_exists($rmDir) && file_exists($rmDir)) {
                rmdir($rmDir);
            }
            $rmDir = dirname($rmDir);
        }
    }
    public function setUp()
    {
        $this->cleanup();
    }
    public function tearDown()
    {
        $this->cleanup();
    }

    public function testCreate()
    {
        $res = Fs::getAbsoluteResource(Fs::TYPE_FILE, $testFilename = self::getTestfilename());
        $res->tryCreate();
        $this->assertFileExists($testFilename);
        $res->tryCreate();
        // try again without warning
    }
}