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

namespace Foomo\Log;

use PHPUnit_Framework_TestCase as TestCase;

/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
class UtilsTest extends TestCase {
	public function testGetSesssions()
	{
		$utils = new Utils();
		$utils->setFile(Mock::getMockLog());
		/* @var $userSession UserSession */
		foreach($utils->getSessions() as $sessionId => $userSession) {
			$this->assertInstanceOf('Foomo\\Log\\UserSession', $userSession);
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