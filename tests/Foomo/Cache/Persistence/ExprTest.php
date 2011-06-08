<?php

namespace Foomo\Cache\Persistence;

use \Foomo\Cache\Persistence\Expr;

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