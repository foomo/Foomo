<?php

namespace Foomo\MVC;

/**
 * a mock controller
 * //@todo give it another name and its own file
 *
 */
class TestController {
  const CONTROLLER_ID = 'testModule';
  public function actionTest($a, $b = 11)
  {
    return (integer) $a + $b;
  }
  public function actionDefault()
  {
    return 'default';
  }
}