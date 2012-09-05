<?php

/*
 * This file is part of the foomo Opensource Framework.
 * 
 * The foomo Opensource Framework is free software: you can redistribute it
 * and/or modify it under the terms of the GNU Lesser General Public License as
 * published  by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 * 
 * The foomo Opensource Framework is distributed in the hope that it will
 * be useful, but WITHOUT ANY WARRANTY; without even the implied warranty
 * of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public License along with
 * the foomo Opensource Framework. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Foomo\Jobs;
 
/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author Jan Halfar jan@bestbytes.com
 */
class UtilsTest extends \PHPUnit_Framework_TestCase
{
	public function testCollectJobs()
	{
		$jobs = Utils::collectJobs();
		$this->assertArrayHasKey(\Foomo\Module::NAME, $jobs);
		$enabledModules = \Foomo\Modules\Manager::getEnabledModules();
		$sessionEnabled = \Foomo\Session::getEnabled();
		$sessionGCFound = false;
		$fileGCFound = false;
		foreach($jobs as $module => $jobs) {
			$this->assertTrue(in_array($module, $enabledModules));
			foreach($jobs as $job) {
				$this->assertInstanceOf(__NAMESPACE__ . '\\AbstractJob', $job);
				if($module == \Foomo\Module::NAME && $sessionEnabled && $job instanceof \Foomo\Session\GCJob) {
					$sessionGCFound = true;
				}
				if($job instanceof Common\FileGC) {
					$fileGCFound = true;
				}
			}
		}
		$this->assertTrue($fileGCFound, 'could not find a file gc');
		if($sessionEnabled) {
			$this->assertTrue($sessionGCFound, 'session gc job was not found');
		}
	}
}