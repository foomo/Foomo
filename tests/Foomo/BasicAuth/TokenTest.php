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

namespace Foomo\BasicAuth;

/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
class TokenTest extends \PHPUnit_Framework_TestCase {
	public function setUp()
	{
		Token\GC::collectGarbage(-1);
	}
	public function testCreateToken()
	{
		$this->assertNotEmpty(Token::createTokenForUser("foo", ["test"]));
	}
	public function testUseToken()
	{
		$this->assertNotEmpty($password = Token::createTokenForUser($user = "foo", $domains = ["test", "foobar"]));
		$this->assertEquals($domains, Token::useToken($user, $password));
		$this->assertEquals([], Token::useToken($user, $password));

		$this->assertNotEmpty($password = Token::createTokenForUser($user = "foo", $domains = ["test", "foobar"]));
		sleep(3);
		$this->assertEquals([], $domains = Token::useToken($user, $password, 1), "domains should have been expired after one second " . serialize($domains));
	}
}