<?php

/*
 * bestbytes-copyright-placeholder
 */

namespace Foomo\Modules\Frontend;

use Foomo\Modules\Manager;
use Foomo\MVC;
use Foomo\Modules\Utils;

/**
 * controller
 */
class Controller {

	/**
	 * model
	 * 
	 * @var Foomo\Modules\Frontend\Model
	 */
	public $model;

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

	public function actionShowMVCApp($moduleName)
	{
		$this->model->validateModule($moduleName);
		$this->model->currentModuleApp = Manager::getModuleMVCFrontEndClassName($moduleName);
	}

	public function actionCreateNew()
	{
		
	}

	public function actionAdminister()
	{
		
	}

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
			
		}
	}

	/**
	 * @param string $moduleName name of the module to create resources for
	 */
	public function actionTryCreateModuleResources($moduleName)
	{
		$this->model->resourceCreationReport = Manager::tryCreateModuleResources($moduleName);
	}

	public function actionTryCreateAllModuleResources()
	{
		$this->model->resourceCreationReport = '';
		foreach (Manager::getEnabledModules() as $enabledModuleName) {
			$this->model->resourceCreationReport .= Manager::tryCreateModuleResources($enabledModuleName);
		}
	}

}