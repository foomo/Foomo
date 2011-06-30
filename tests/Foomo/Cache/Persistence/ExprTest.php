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

namespace Foomo\Cache\Persistence;

use \Foomo\Cache\Persistence\Expr;

/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
class ExprTest extends \PHPUnit_Framework_TestCase {


    public function setUp() {}


    public function testExpr() {
	$expr = Expr::propEq('id', '123');

	//var_dump($expr->value);

	$expr = Expr::groupAnd(Expr::idEq('123456789'),
			      Expr::propEq('id', '123'),
			      Expr::propEq('status', 1),
			      Expr::hitsLessThan(10),
			      Expr::createdAfter(\time()),
		              Expr::groupOr(Expr::propNe('id', '123'),
			                   Expr::propsEq(array('n1'=>'v1','n2'=>'v2')),
					    Expr::groupOr(Expr::statusValid(),
							  Expr::statusInvalid(),
						          Expr::isExpired('fast'))
			      )
		      );
	//var_dump($expr->value[1][0]->value[0]);
	//var_dump($expr->value[1][0]->value[1]);

	$this->assertEquals('Foomo\Cache\Persistence\Expr::groupAnd', $expr->value[0]);
	$this->assertEquals('Foomo\Cache\Persistence\Expr::idEq',$expr->value[1][0]->value[0]);
	$this->assertEquals('123456789', $expr->value[1][0]->value[1][0]);

    }
}