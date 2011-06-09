<?php

namespace Foomo\Log;

use PHPUnit_Framework_TestCase as TestCase;

class UtilsTest extends TestCase {
	public function testGetSesssions()
	{
		$utils = new Utils();
		$utils->setFile(Mock::getMockLog());
		/* @var $userSession UserSession */
		foreach($utils->getSessions() as $sessionId => $userSession) {
			$this->assertTrue($userSession instanceof UserSession);
			$this->assertEquals($sessionId, $userSession->sessionId);
		}
	}
	public function testGetFilterProviders()
	{
		$filterProviders = Utils::getFilterProviders();
		$keys = array_keys($filterProviders);
		$this->assertTrue(in_array(\Foomo\Module::NAME, $keys), 'there should be a filter provider in module ' . \Foomo\Module::NAME);
		$this->assertTrue(
			isset($filterProviders[\Foomo\Module::NAME]) && 
			is_array($filterProviders[\Foomo\Module::NAME]) &&
			$filterProviders[\Foomo\Module::NAME]['Foomo\\Log\\Filters\\CommonFilters'] &&
			$filterProviders[\Foomo\Module::NAME]['Foomo\\Log\\Filters\\CommonFilters']['allBadThings']
		);
	}
}