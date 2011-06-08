<?php

/*
 * bestbytes-copyright-placeholder
 */

namespace Foomo\Config\Spec;

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
			->whenOldConfigsAreRemoved()
			->thenNoOldConfigExists()
		;
		
	}
	public function tearDown()
	{
		$this->world->cleanUp();
	}
	private function cleanup()
	{
		
	}
}