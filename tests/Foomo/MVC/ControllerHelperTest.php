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

namespace Foomo\MVC;

/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
class ControllerHelperTest extends \PHPUnit_Framework_TestCase {
    /**
     * get an instance
     *
     * @return \Foomo\MVC\ControllerHelper
     */
    private static function getControllerInstance($mode)
    {
      return new  \Foomo\MVC\ControllerHelper($mode);
    }
    public function testGetModeDefaultAction()
    {
      $inst   = self::getControllerInstance(\Foomo\MVC\ControllerHelper::MODE_GET_PARMS);
      $app    = new TestController();
      $result = $inst->control($app);
      $this->assertEquals('default', $result , 'default action not recognized');
    }
    private function requestGet($class, $action, $testParm, $testParmOptional = null)
    {
      $_REQUEST = array(
        'class'  => $class,
        'action' => $action,
        'a'      => $testParm,
        'b'      => $testParmOptional
      );
      $inst   = self::getControllerInstance(\Foomo\MVC\ControllerHelper::MODE_GET_PARMS);
      $app    = new TestController();
      return $inst->control($app);
    }
    private function requestPath($class, $action, $testParm, $testParmOptional = null)
    {
      $inst   = self::getControllerInstance(\Foomo\MVC\ControllerHelper::MODE_PATH);
      $inst->setBaseURI('/somefolder/someDoc');
      $app    = new TestController();
      $_SERVER['REQUEST_URI'] = '/somefolder/someDoc/' . $class;
      if(isset($action)) {
        $_SERVER['REQUEST_URI'] .= '/' . $action;
      }
      if(isset($testParm)) {
        $_SERVER['REQUEST_URI'] .= '/' . urlencode($testParm);
      }
      if(isset($testParmOptional)) {
        $_SERVER['REQUEST_URI'] .= '/' . urlencode($testParmOptional);
      }
      return $inst->control($app);
    }
    public function testGetModeTestAction()
    {
      $this->assertEquals(5, $this->requestGet('testModule', 'test', 3, 2) , 'test action not recognized');
    }
    public function testPathModeDefaultAction()
    {
      $this->assertEquals('default',$this->requestGet('testModule', null, null, null) , 'default action not recognized');
    }
    public function testPathModeTestAction()
    {
      $this->assertEquals(5, $this->requestPath('testModule', 'test', 3,2) , 'test action not recognized');
    }
}
