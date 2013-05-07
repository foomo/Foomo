<?php

class MyMVCRouter extends \Foomo\Router {
	private $app;
	public function __construct()
	{
		parent::__construct();
		// put your MVC app in here
		$this->app = new \My\Frontend;
		// add routes
		$this->addRoutes(array(
			'/home' => array($this->app->controller, 'actionDefault'),
			'/me' => array($this->app->controller, 'actionProfile'),
			'/bye' => array($this->app->controller, 'actionLogout'),
			// "special" default route
			'/' => 'slash'
		));
	}
	public function slash()
	{
		header('Location: /home');
		exit;
	}
	public static function run()
	{
		// this will cause 404s - you want that!
		URLHandler::strictParameterHandling(true);
		// hide the class id in urls
		URLHandler::exposeClassId(false);
		// hide the script - you might need mod rewrite
		MVC::hideScript(true);
		$router = new self();
		return AppRunner::run($router->app, $router, '');
	}
}

echo MyMVCRouter::run();