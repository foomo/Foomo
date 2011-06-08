<?php

namespace Foomo\MVC;

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
