<?php

/*
 * bestbytes-copyright-placeholder
 */

namespace Foomo\Modules\Frontend;

use Foomo\Modules\Manager;
use Exception;

/**
 * module manager model
 */
class Model {
	const ERROR_CODE_MODULE_DOES_NOT_EXIST = 0;
	/**
	 * plain text report of last resource generation
	 * 
	 * @var string
	 */
	public $resourceCreationReport;
	public $currentModuleApp;

	/**
	 * validate if the module is valid
	 * 
	 * @param string $moduleName 
	 */
	public function validateModule($moduleName)
	{
		if (!in_array($moduleName, Manager::getEnabledModules())) {
			throw new Exception('module does not exist ' . $moduleName, self::ERROR_CODE_MODULE_DOES_NOT_EXIST);
		}
	}

}