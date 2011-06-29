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