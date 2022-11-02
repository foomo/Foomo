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

namespace Foomo\Info\Frontend;
use Foomo\Cache\Manager;

/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author franklin <franklin@weareinteractive.com>
 */
class Controller
{
	//---------------------------------------------------------------------------------------------
	// ~ Variables
	//---------------------------------------------------------------------------------------------

	/**
	 * @var \Foomo\Info\Frontend\Model
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
	public function actionPhp()
	{
	}

	/**
	 *
	 */
	public function actionApc()
	{
		$fastPersistor = Manager::getFastPersistor();
		if (get_class($fastPersistor) == 'Foomo\Cache\Persistence\Fast\APCUPersistor') {
			if (version_compare(phpversion('apcu'), '5.0.0') >= 0) {
				$script = 'apcu5.php';
			} else {
				$script = 'apcu.php';
			}
		} elseif (version_compare(phpversion('apc'), '4.0.0') >= 0) {
			$script = 'apcu.php';
		} else {
			$script = 'apc.php';
		}
		header('Location: ' . \Foomo\ROOT_HTTP . '/' . $script);
		exit;
	}

	/**
	 *
	 */
	public function actionMemcache()
	{
		header('Location: ' . \Foomo\ROOT_HTTP . '/memcache.php');
	}

	/**
	 *
	 */
	public function actionOpCache()
	{
		header('Location: ' . \Foomo\ROOT_HTTP . '/opcache.php');
	}
}