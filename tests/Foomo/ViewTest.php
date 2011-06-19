<?php

namespace Foomo;

class ViewTest extends \PHPUnit_Framework_TestCase {
	private function getTestView()
	{
		return \Foomo\Module::getView($this, 'ViewTest', 'modelViewTest');
	}
	public function testViewFromFile()
	{
		$this->assertInstanceOf('Foomo\\View', $this->getTestView());
	}
	public function testRender()
	{
		$view = $this->getTestView();
		$expected = 'ViewTest
Line 0 : modelViewTest
Line 1
Line 2';
		$result = $view->render();
		$this->assertEquals($expected, $result);
	}
}