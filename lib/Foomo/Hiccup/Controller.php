<?php

/*
 * bestbytes-copyright-placeholder
 */

namespace Foomo\Hiccup;

use Foomo\Hiccup;

/**
 * system hiccup handling - when the system does not bootstrap any more
 * @internal
 */
final class Controller {
	const CONTROLLER_ID = 'hiccup';
	/**
	 * model
	 * 
	 * @var Foomo\Hiccup
	 */
	private $model;

	public function __construct()
	{
		$this->model = new Hiccup();
	}

	private function renderMenu()
	{
		return $this->getView('index')->render();
	}

	private function renderStatus()
	{
		return $this->getView('status')->render();
	}

	private function getView($template, $model = null)
	{
		$templateFile = \Foomo\ROOT . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . \Foomo\Module::NAME . DIRECTORY_SEPARATOR . 'Hiccup' . DIRECTORY_SEPARATOR . $template . '.tpl';
		if (is_null($model)) {
			$model = $this->model;
		}
		return \Foomo\View::fromFile($templateFile, $model);
	}

	public function actionDefault()
	{
		return $this->renderMenu() . $this->renderStatus();
	}

	public function actionResetAutoloader()
	{
		$ret = $this->renderMenu();
		$model = array('action' => 'remove autoloader', 'result' => Hiccup::removeAutoloaderCache());
		$ret .= $this->getView('result', $model);
		return $ret;
	}

	public function actionResetConfigCache()
	{
		$ret = $this->renderMenu();
		$model = array('action' => 'remove config cache', 'result' => Hiccup::removeConfigCache());
		$ret .= $this->getView('result', $model);
		return $ret;
	}

}
