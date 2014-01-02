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

namespace Foomo\Router;

use PHPUnit_Framework_TestCase as TestCase;

/**
 * a router
 *
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
class RegexTest extends TestCase
{
	public $command;
	public $parameters;

	/**
	 * @param $regex
	 * @return Regex
	 */
	private function getRegex($matchingRegex, $renderRegex)
	{
		return new Regex($matchingRegex, $renderRegex);
	}
	public function testRegexParse()
	{
		$regex = $this->getRegex('/\/(?P<region>[a-z]{2})\/(?P<language>[a-z]{2})\//', '//');
		$this->assertEquals(array('region', 'language'), $regex->parameters);
	}
	public function testRegexMatch()
	{
		$regex = $this->getRegex('/\/(?P<region>[a-z]{2})\/(?P<language>[a-z]{2})\//', '//');
		$path = '/de/by/blablabla';
		$this->assertTrue($regex->matches($path));
		$this->assertEquals(array('region' => 'de', 'language' => 'by'), $regex->extractParameters($path));
	}
	public function testRenderURL()
	{
		$regex = $this->getRegex('/\/(?P<region>[a-z]{2})\/(?P<language>[a-z]{2})\//', '/go/:region/:language');
		$this->assertEquals('/go/de/by', $regex->url(array('region' => 'de', 'language' => 'by')));
	}
}