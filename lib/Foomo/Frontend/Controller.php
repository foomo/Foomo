<?php

/*
 * bestbytes-copyright-placeholder
 */

namespace Foomo\Frontend;

/**
 * toolbox controller
 */
class Controller {

	/**
	 * model
	 *
	 * @var Foomo\Frontend\Model
	 */
	public $model;

	public function actionDefault()
	{
		
	}

	public function actionShowFrame($url)
	{
		$this->model->currentFrameUrl = $url;
	}

	public function actionLogViewer()
	{
	}

	public function actionCache()
	{
		
	}

	public function actionLog()
	{
		
	}

	public function actionConfig()
	{
		
	}

	public function actionBasicAuth()
	{
		
	}

	public function actionModules()
	{
		
	}
	
	
	public function actionResetAutoloader() {
		$this->model->classMap = \Foomo\Autoloader::resetCache();
	}

	public function actionInfo($type = '', $block = '')
	{
		$this->model->showInfo($type, $block);
	}

}