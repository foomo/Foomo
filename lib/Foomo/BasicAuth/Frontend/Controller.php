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

namespace Foomo\BasicAuth\Frontend;

use Foomo\BasicAuth\Utils;
use Foomo\MVC;

class Controller {
	/**
	 * @var Foomo\BasicAuth\Frontend\Model
	 */
	public $model;
	public function actionDefault() {}
	public function actionUpdateUser($domain, $user, $password)
	{
		Utils::updateUser($domain, $user, $password);
		MVC::redirect('showDomain', array($domain));
	}
	public function actionDeleteDomain($domain)
	{
		Utils::deleteDomain($domain);
	}
	public function actionCreateDomain($domain)
	{
		if(!empty($domain)) {
			Utils::createDomain($domain);
		}
	}
	public function actionDeleteUser($domain, $user)
	{
		Utils::deleteUser($domain, $user);
		MVC::redirect('showDomain', array($domain));
	}
	public function actionShowDomain($domain)
	{
		$this->model->currentAuthDomain = $domain;
	}
}