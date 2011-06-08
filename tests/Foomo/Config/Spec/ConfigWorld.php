<?php

namespace Foomo\Config\Spec;

use Foomo\Config\Utils;

class ConfigWorld {
	public $testDomains = array('none' => null, 'mock' => 'myMockTestDomain');
	public function cleanUp()
	{
		Utils::removeOldConfigs();
		$oldConfigs = \Foomo\Config\Utils::getOldConfigs();
		foreach($oldConfigs as $oldConfig) {
			/* @var $oldConfig \Foomo\Config\OldConfig */
			if($oldConfig->domain == DomainConfig::NAME) {
				\unlink($oldConfig->filename);
			}
		}
		foreach($this->testDomains as $testDomain) {
			if(\Foomo\Config::confExists(\Foomo\Module::NAME, DomainConfig::NAME, $testDomain)) {
				\Foomo\Config::removeConf(\Foomo\Module::NAME, DomainConfig::NAME, $testDomain);
			}
		}
	}
	/**
	 * @var \PHPUnit_Framework_TestCase
	 */
	public $testCase;
	/**
	 * @story given config does not exist for <?= $name . '/' . $domain ?> 
	 * 
	 * @param string $module
	 * @param string $name
	 * @param string $domain
	 * 
	 * @return Foomo\Config\Spec\ConfigWorld
	 */
	public function givenConfigDoesNotExist($module, $name, $domain) {
		$this->testCase->assertFalse(\Foomo\Config::confExists($module, $name, $domain));
	}
	/**
	 * @story when config is created <?= $name . '/' . $domain ?> 
	 * 
	 * @param string $module 
	 * @param string $name 
	 * @param string $domain
	 * 
	 * @return Foomo\Config\Spec\ConfigWorld
	 */
	public function whenConfigIsCreatedFromDefault($module, $name, $domain)
	{
		\Foomo\Config::setConf(\Foomo\Config::getDefaultConfig($name), $module, $domain);
	}
	/**
	 * @story then config exists for <?= $name . '/' . $domain ?> 
	 * @param string $module
	 * @param string $name
	 * @param unknown $domain
	 * 
	 * @return Foomo\Config\Spec\ConfigWorld
	 */
	public function thenConfigExists($module, $name, $domain) 
	{
		$this->testCase->assertTrue(\Foomo\Config::confExists($module, $name, $domain));
	}	
	
	/**
	 * @story given no old config exists
	 * @return Foomo\Config\Spec\ConfigWorld
	 */
	public function givenNoOldConfigExists() {
		echo 'story step ' . __METHOD__ . ' needs to be implemented';
	}
	/**
	 * @story when config is set
	 * @param string $module name of the module
	 * @param Foomo\Config\Spec\DomainConfig $config config inst
	 * @param string $domain config domain
	 * @return Foomo\Config\Spec\ConfigWorld
	 */
	public function whenConfigIsSet($module, DomainConfig $config, $domain) {
		\Foomo\Config::setConf($config, $module, $domain);
	}
	/**
	 * @story then old config "<?= $configName ?>" exists for module "<?= $module ?>" in domain "<?= $domain ?>"
	 * @param string $module comment
	 * @param string $configName comment
	 * @param string $domain comment
	 * @return Foomo\Config\Spec\ConfigWorld
	 */
	public function thenOldConfigExists($module, $configName, $domain) {
		foreach(Utils::getOldConfigs() as $oldConfig) {
			/* @var $oldConfig \Foomo\Config\OldConfig */
			if(
				$oldConfig->domain == $domain &&
				$oldConfig->module == $module &&
				$oldConfig->name == $configName
			) {
				return;
			}
		}
		$this->testCase->fail(
			'could not find old config name: ' . $configName .
			',  for module: ' . $module .
			', in config domain: '. $domain
		);
	}
	/**
	 * @story when old configs are removed
	 * @return Foomo\Config\Spec\ConfigWorld
	 */
	public function whenOldConfigsAreRemoved() {
		Utils::removeOldConfigs();
	}
	/**
	 * @story then no old config exists
	 * @return Foomo\Config\Spec\ConfigWorld
	 */
	public function thenNoOldConfigExists() {
		$oldConfigs = Utils::getOldConfigs();
		$oldConfigCount = count($oldConfigs);
		$this->testCase->assertTrue($oldConfigCount == 0, 'old config count should have been 0 got: ' . $oldConfigCount );
	}	
	
}