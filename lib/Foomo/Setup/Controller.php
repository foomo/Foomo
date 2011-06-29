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

namespace Foomo\Setup;

use Foomo\Config;

/**
 * framework setup
 * @internal
 */
class Controller {
	const CONTROLLER_ID = 'setup';
	public $error;
	public $baseResourceError;

	public function actionDefault()
	{
		return $this->render();
	}

	public function actionSetupAdminUser($userName, $password, $passwordRepeat)
	{
		if(\Foomo\Setup::getDefaultAuthWasSetUp()) {
			$this->error = 'Hohoho, there already is a basic auth';
		} else {
			if (
				!empty($password) &&
				($password == $passwordRepeat) &&
				!empty($userName)
			) {
				\Foomo\BasicAuth\Utils::updateUser(\Foomo\BasicAuth::DEFAULT_AUTH_DOMAIN, $userName, $password);
			} else {
				$this->error = 'posted data are invalid';
			}
		}
		return $this->render();		
	}

	private function render()
	{
		return \Foomo\Module::getView($this, 'index', $this);
	}

}