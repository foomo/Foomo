<?php

/*
 * bestbytes-copyright-placeholder
 */

namespace Foomo\Frontend;

/**
 * toolbox controller
 */
class Controller
{
	//---------------------------------------------------------------------------------------------
	// ~ Variables
	//---------------------------------------------------------------------------------------------

	/**
	 * @var Foomo\Frontend\Model
	 */
	public $model;

	//---------------------------------------------------------------------------------------------
	// ~ Action methods
	//---------------------------------------------------------------------------------------------

	/**
	 *
	 */
	public function actionDefault()
	{
	}

	/**
	 *
	 */
	public function actionLogout()
	{
		header('WWW-Authenticate: Basic realm="' . \Foomo\Frontend::BASIC_AUTH_REALM . '", true, 401');
	}

	/**
	 * @param string $url
	 */
	public function actionShowFrame($url)
	{
		$this->model->currentFrameUrl = $url;
	}

	/**
	 * @param string $mvcAppName
	 */
	public function actionShowMVCApp($app, $action)
	{
		$this->model->currentModuleApp = str_replace('.', '\\', $app);
		$this->model->updateNavi($app, $action);
	}

	/**
	 * @param string $type
	 * @param string $block
	 */
	public function actionInfo($type='', $block='')
	{
		$this->model->showInfo($type, $block);
	}

	/**
	 *
	 */
	public function actionResetAutoloader()
	{
		$this->model->classMap = \Foomo\Autoloader::resetCache();
	}
}