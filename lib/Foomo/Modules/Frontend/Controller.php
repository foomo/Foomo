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

namespace Foomo\Modules\Frontend;

use Foomo\Modules\Manager;
use Foomo\MVC;
use Foomo\Modules\Utils;

/**
 * controller
 * 
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
	 * model
	 *
	 * @var Foomo\Modules\Frontend\Model
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
	 * enable some disable some
	 *
	 * @param array $moduleStates hash true or false for every module
	 */
	public function actionUpdateModules($moduleStates)
	{
		Manager::setModuleStates($moduleStates);
		MVC::redirect('administer');
	}

	/**
	 * @param string $moduleName
	 */
	public function actionShowMVCApp($moduleName)
	{
		$this->model->validateModule($moduleName);
		$this->model->currentModuleApp = Manager::getModuleMVCFrontEndClassName($moduleName);
	}

	/**
	 *
	 */
	public function actionCreateNew()
	{

	}

	/**
	 *
	 */
	public function actionAdminister()
	{

	}

	/**
	 * @param string $name
	 * @param string $description
	 * @param string $requiredModules
	 */
	public function actionCreateModule($name, $description, $requiredModules)
	{
		try {
			$req = explode(',', $requiredModules);
			$requiredModules = array();
			foreach ($req as $r) {
				$requiredModules[] = trim($r);
			}
			Utils::createModule($name, $description, $requiredModules);
			MVC::redirect('administer');
		} catch (Exception $e) {
			// @todo: display error
		}
	}

	/**
	 * @param string $moduleName name of the module to create resources for
	 */
	public function actionTryCreateModuleResources($moduleName)
	{
		$this->model->resourceCreationReport = Manager::tryCreateModuleResources($moduleName);
	}

	/**
	 *
	 */
	public function actionTryCreateAllModuleResources()
	{
		$this->model->resourceCreationReport = '';
		foreach (Manager::getEnabledModules() as $enabledModuleName) {
			$this->model->resourceCreationReport .= Manager::tryCreateModuleResources($enabledModuleName);
		}
	}
}