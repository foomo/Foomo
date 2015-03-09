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

namespace Foomo\Hiccup;

use Foomo\Hiccup;

/**
 * system hiccup handling - when the system does not bootstrap any more
 *
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
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

	public function actionDisableAllModules()
	{
		$ret = $this->renderMenu();
		$model = array('action' => 'disable all modules', 'result' => Hiccup::disableAllModules());
		$ret .= $this->getView('result', $model);
		return $ret;
	}

	public function actionResetEverything()
	{
		$ret = $this->renderMenu();
		$model = array('action' => 'reset everything (modules, config cache, autoloader)', 'result' => Hiccup::resetEverything());
		$ret .= $this->getView('result', $model);
		return $ret;
	}

}
