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

/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
class Controller
{
	//---------------------------------------------------------------------------------------------
	// ~ Variables
	//---------------------------------------------------------------------------------------------

	/**
	 * @var Foomo\BasicAuth\Frontend\Model
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
	 * @param string $domain
	 * @param string $user
	 * @param string $password
	 */
	public function actionUpdateUser($domain, $user, $password)
	{
		Utils::updateUser($domain, $user, $password);
		MVC::redirect('showDomain', array($domain));
	}

	/**
	 * @param string $domain
	 */
	public function actionDeleteDomain($domain)
	{
		Utils::deleteDomain($domain);
	}

	/**
	 * @param string $domain
	 */
	public function actionCreateDomain($domain)
	{
		if(!empty($domain)) {
			Utils::createDomain($domain);
		}
	}

	/**
	 * @param string $domain
	 * @param string $user
	 */
	public function actionDeleteUser($domain, $user)
	{
		Utils::deleteUser($domain, $user);
		MVC::redirect('showDomain', array($domain));
	}

	/**
	 * @param string $domain
	 */
	public function actionShowDomain($domain)
	{
		$this->model->currentAuthDomain = $domain;
	}
}