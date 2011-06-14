<?php

// My.Module/lib/My/Module/Frontend/Controller.php

namespace My\Module\Frontend;

use Foomo\MVC;

class Controller {

	/**
	 * @var Model
	 */
	public $model;
	public function actionDefault() {}
	/**
	 * do foo
	 *
	 * @param string $bar bar
	 */
	public function actionHello($bar) 
	{
		$this->model->hello = $bar;
	}
	public function actionFoo()
	{
		// ...
		// will redirect and terminate
		MVC::redirect('bar');
	}
	/**
	 * stream an object
	 * 
	 * @param string $id
	 */
	public function actionStreamObject($id)
	{
		// html output would be in the way =>
		// abort the MVC app
		MVC::abort();
		// ...
		exit;
	}
	/**
	 * some form thing
	 * 
	 * @param string $foo
	 * @param string $bar 
	 */
	public function actionFormExample($foo, $bar) {}
}