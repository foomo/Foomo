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

namespace Foomo\Config\Spec;

/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
class ConfigSpec extends \Foomo\TestRunner\AbstractSpec {
	/**
	 * @var ConfigWorld
	 */
	public $world;
	public function setUp()
	{
		$this->setWorld(new ConfigWorld);
		$this->world->cleanUp();
	}
	public function testScenarioCreation()
	{
		foreach($this->world->testDomains as $domain) {
			$this->world
				->givenConfigDoesNotExist($module = \Foomo\Module::NAME, $name = DomainConfig::NAME, $domain)
				->whenConfigIsCreatedFromDefault($module, $name, $domain)
				->thenConfigExists($module, $name, $domain)
			;
		}
	}
	public function testScenarioOldConfigs()
	{
		$this->world
			->givenNoOldConfigExists()
			->whenConfigIsSet($module = \Foomo\Module::NAME, $config = new DomainConfig, $domain = 'test')
			->whenConfigIsSet($module, $config, $domain)
			->thenOldConfigExists($module, $configName = DomainConfig::NAME, $domain)
			->whenConfigIsSet($module, $config, $domainA = 'testA')
			->whenOldConfigsAreRemoved($module, null, $domainA)
			->thenOldConfigExists($module, $configName, $domain)
			->whenOldConfigsAreRemoved($module, $configName)
			->thenNoOldConfigExists()	
			->whenConfigIsSet($module, $config, $domain)
			->whenOldConfigsAreRemoved()
			->thenNoOldConfigExists()
		;
	}
	public function tearDown()
	{
		$this->world->cleanUp();
	}
}