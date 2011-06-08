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
}