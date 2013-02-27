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

namespace Foomo\Frontend;

/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 * @author franklin <franklin@weareinteractive.com>
 */
class Controller
{
	//---------------------------------------------------------------------------------------------
	// ~ Variables
	//---------------------------------------------------------------------------------------------

	/**
	 * @var Model
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
	 * @param string $app
	 * @param string $action
	 * @param string $parameters
	 */
	public function actionShowMVCApp($app=null, $action='default', $parameters=array())
	{
		$this->model->currentModuleApp = str_replace('.', '\\', $app) . '\\Frontend';
		$this->model->updateNavi($app, $action, $parameters);
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