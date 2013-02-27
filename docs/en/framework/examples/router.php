<?php

class MyDelegate {
	public function bar()
	{
		echo 'bar';
	}
}

class TestRouter extends \Foomo\Router {
	public function __construct()
	{
		parent::__construct();
		$delegate = new MyDelegate();
		$this->addRoutes(array(
			// routing to a delegate
			'/delegate-bar' => array($delegate, 'bar'),     // /delegate-bar
			// routing to myself

			// order matters: complicated first

			// greetings
			'/greet/name-:name:.html' => 'greet',           // /greet/name-Charles.html
			'/greet/name-:name' => 'greet',                 // /greet/name-Charles
			'/greet/:name' => 'greet',                      // /greet/Charles

			// others
			'/foo' => 'foo',                                // /foo
			'/' => 'index',                                 // /
			'/*' => 'all'	                                // /... anything else
		));
	}
	public function index()
	{
		echo 'index';
	}
	public function greet($name)
	{
		// warning $name is NOT sanitized - do not trust it
		echo 'Hello ' . $name;
	}
	public function foo()
	{
		echo 'Hello World';
	}
	public function all()
	{
		echo 'in case of doubt it is me ' . $this->currentPath;
	}
}
$router = new TestRouter();
$router->execute();