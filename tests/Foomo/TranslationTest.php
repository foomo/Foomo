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

/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
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
		$defaultChain = $_SERVER['HTTP_ACCEPT_LANGUAGE'];

		$_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'fr';
		$this->assertEquals(['fr'], Translation::getDefaultChainFromEnv([]), 'failed to parse "fr"');

		$_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'fr;q=0.5';
		$this->assertEquals(['fr'], Translation::getDefaultChainFromEnv([]), 'failed to parse "fr;q=0.5"');

		$_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'fr;q=0.5';
		$this->assertEquals(['fr', 'it'], Translation::getDefaultChainFromEnv(['it']), 'failed to parse "fr;q=0.5"');

		$_SERVER['HTTP_ACCEPT_LANGUAGE'] = '*';
		$this->assertEquals(['it'], Translation::getDefaultChainFromEnv(['it']), 'failed to parse "*"');

		$_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'de;q=0.6,*;q=0.8';
		$this->assertEquals(['de', 'it'], Translation::getDefaultChainFromEnv(['it']), 'failed to parse "de,*:q=0.8"');

		$_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en-us,de-de;q=0.7,en;q=0.3';
		$this->assertEquals(['en_US', 'de_DE', 'en', 'de'], Translation::getDefaultChainFromEnv(), 'failed to parse "en-us,de-de;q=0.7,en;q=0.3"');

		$_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en-US,en;q=0.8,de-DE;q=0.6,de;q=0.4';
		$this->assertEquals(['en_US', 'en', 'de_DE', 'de'], Translation::getDefaultChainFromEnv(), 'failed to parse "en-US,en;q=0.8,de-DE;q=0.6,de;q=0.4"');

		$_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en-US,en;q=0.2,de-DE;q=0.6,de;q=0.4';
		$this->assertEquals(['en_US', 'de_DE', 'de', 'en'], Translation::getDefaultChainFromEnv(), 'failed to parse "en-US,en;q=0.2,de-DE;q=0.6,de;q=0.4"');

		$_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en,de';
		$this->assertEquals(['en', 'de'], Translation::getDefaultChainFromEnv(), 'failed to parse "en,de"');

		$_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en;q=0.2,de;q=0.2';
		$this->assertEquals(['en', 'de'], Translation::getDefaultChainFromEnv(), 'failed to parse "en;q=0.2,de;q=0.2"');

		$_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'de;q=0.2,en;q=0.2';
		$this->assertEquals(['de', 'en'], Translation::getDefaultChainFromEnv(), 'failed to parse "en;q=0.2,de;q=0.2"');

		$_SERVER['HTTP_ACCEPT_LANGUAGE'] = '*';
		$this->assertEquals(['en', 'de'], Translation::getDefaultChainFromEnv(), 'failed to parse "*"');

		$_SERVER['HTTP_ACCEPT_LANGUAGE'] = $defaultChain;
	}
	public function testCachedGetLocaleTable()
	{
		$appClassName = 'Foomo\\Frontend';
		$namespace = 'Foomo\\Frontend';
		$localeRoots = \Foomo\MVC::getLocaleRoots($appClassName);

		$localeChain = ['de'];
		$localeTable = Translation::cachedGetLocaleTable($localeRoots, $localeChain, $namespace);
		$this->assertTrue(is_array($localeTable), 'locale table is not an array');
		$this->assertTrue(count($localeTable) > 0, 'empty locale table');
		$this->assertArrayHasKey('GREET_DEFAULT', $localeTable);

		$localeChain = ['en'];
		$localeTable = Translation::cachedGetLocaleTable($localeRoots, $localeChain, $namespace);
		$this->assertTrue(is_array($localeTable), 'locale table is not an array');
		$this->assertTrue(count($localeTable) > 0, 'empty locale table');
		$this->assertArrayHasKey('GREET_DEFAULT', $localeTable);

		$localeChain = ['de_DE'];
		$localeTable = Translation::cachedGetLocaleTable($localeRoots, $localeChain, $namespace);
		$this->assertTrue(is_array($localeTable), 'locale table is not an array');
		$this->assertTrue(count($localeTable) > 0, 'empty locale table');
		$this->assertArrayHasKey('GREET_DEFAULT', $localeTable);

		$localeChain = ['fr_CH', 'en_GB'];
		$localeTable = Translation::cachedGetLocaleTable($localeRoots, $localeChain, $namespace);
		$this->assertTrue(is_array($localeTable), 'locale table is not an array');
		$this->assertTrue(count($localeTable) > 0, 'empty locale table');
		$this->assertArrayHasKey('GREET_DEFAULT', $localeTable);

		$localeChain = ['ru'];
		$localeTable = Translation::cachedGetLocaleTable($localeRoots, $localeChain, $namespace);
		$this->assertTrue(is_array($localeTable), 'locale table is not an array');
		$this->assertTrue(count($localeTable) == 0, 'empty locale table');
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
	public function testLocaleChainFallBack()
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