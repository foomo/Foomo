<?php

/*
 * bestbytes-copyright-placeholder
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