<?php

namespace Foomo\Reflection;

/**
 * a mock class
 * 
 * @property-read integer seppRead a test integer
 * @property-write integer seppWrite a test integer
 * @property integer seppReadWrite a test integer
 */
class MockClass {

	/**
	 * foo prop
	 * 
	 * @var Foomo\Reflection\MockClass
	 */
	public $foo;
	/**
	 * foo takes bar and returns foobar
	 * 
	 * @param string $bar bar bar bar
	 * @param array $fooBar foo bar comment
	 * 
	 * @return string well it returns a poem
	 * @throws Exception
	 * @serviceMessage MyMessage
	 * @serviceGen ignore
	 * @wsdlGen ignore
	 * @see somewhere else
	 * @author jan
	 */
	public function foo($bar, $fooBar)
	{
		
	}

}