<?php

namespace Foomo;

use Foomo\Cache\Manager;

class TranslationTest extends \PHPUnit_Framework_TestCase {
	/**
	 * my locale
	 *
	 * @var Foomo\Translation
	 */
	protected $locale;
	public function setUp()
	{
		//Manager::reset(Manager::getResourceName('Foomo\\Translation', 'cachedGetLocaleTable'), true);
		$baseDir = dirname(__FILE__ ) .\DIRECTORY_SEPARATOR . 'translationResources';
		$this->locale = new Translation(
			array( 
				$baseDir . DIRECTORY_SEPARATOR . 'rootTwo', 
				$baseDir . DIRECTORY_SEPARATOR . 'rootOne'
			), 
			__NAMESPACE__, 
			array('de', 'en')
		);
	}
	public function testGetDefaultChainFromEnv()
	{
		$_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en-us,de-de;q=0.7,en;q=0.3';
		$this->assertEquals(array('en_US', 'en', 'de_DE', 'de'), Translation::getDefaultChainFromEnv());
	}
	public function testGetMesseageSingular()
	{
		$this->assertEquals('rootTwo-de', $this->locale->_('test'));
	}
	public function testGetMesseagePlural()
	{
		$this->assertEquals('rootTwo-de-plural', $this->locale->_( array('test' => 1, 'tests' => 2), 3));
	}
	public function testGetMesseageDual()
	{
		$this->assertEquals('rootTwo-de-dual', $this->locale->_( array('test' => 1, 'dual' => 2, 'tests' => 3), 2));
	}
	public function testInheritanceOverride()
	{
		$this->assertEquals('rootTwo-de', $this->locale->_('test'));
	}
	public function testLcaleChainFallBack()
	{
		$this->assertEquals('fallback-rootOne-de', $this->locale->_('fallback'));
	}
	public function testModuleTranslation()
	{
		$translation = Translation::getModuleTranslation(Module::NAME, 'Foomo\\Frontend', array('en', 'de'));
		$this->assertEquals('Hello %s !', $translation->_('GREET_DEFAULT'));
		
		$translation = Translation::getModuleTranslation(Module::NAME, 'Foomo\\Frontend', array('de', 'en'));
		$this->assertEquals('Servus %s !', $translation->_('GREET_DEFAULT'));
		
	}
}