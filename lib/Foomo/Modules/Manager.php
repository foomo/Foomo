<?php

/*
 * bestbytes-copyright-placeholder
 */

namespace Foomo\Modules;

use DirectoryIterator;
use Foomo\Timer;
use Foomo\Cache\Proxy as CacheProxy;
use Foomo\Cache\Manager as CacheManager;
use Foomo\AutoLoader;
use ReflectionClass;
use Foomo\Config;
use Foomo\Utils;
use Foomo\Cache\Invalidator as CacheInvalidator;
use Foomo\Core\DomainConfig;

/**
 * module management
 */
class Manager {
	const MODULE_STATUS_OK = 'MODULE_STATUS_OK';
	const MODULE_STATUS_REQUIRED_MODULES_MISSING = 'MODULE_STATUS_REQUIRED_MODULES_MISSING';
	const MODULE_STATUS_INVALID = 'MODULE_STATUS_INVALID';
	const MODULE_STATUS_RESOURCES_INVALID = 'MODULE_STATUS_RESOURCES_INVALID';

	/**
	 * get all available modules
	 * every folder in modules will be interpreted as a module definition
	 * 
	 * @return string[]
	 */
	public static function getAvailableModules()
	{
		$dir = new DirectoryIterator(\Foomo\CORE_CONFIG_DIR_MODULES);
		$ret = array();
		foreach ($dir as $item) {
			if (($item instanceof DirectoryIterator) && !$item->isDot() && $item->isDir() && substr($item->getFilename(), 0, 1) != '.') {
				//$moduleClassFilename = \Foomo\CORE_CONFIG_DIR_MODULES . DIRECTORY_SEPARATOR . $item->getFilename() . DIRECTORY_SEPARATOR .
				if (file_exists(self::getModuleClassFileName($item->getFilename()))) {
					$ret[] = $item->getFilename();
				}
			}
		}
		return $ret;
	}
	public static function getModuleVersion($module)
	{
		if(in_array($module, self::getAvailableModules())) {
			$moduleClassName = self::getModuleClassByName($module);
			return constant($moduleClassName . '::VERSION');
		}
	}
	/**
	 * @internal
	 */
	public static function checkModuleConfig()
	{
		while (true) {
			$enabledModules = self::getEnabledModules();
			$done = true;
			foreach (self::getEnabledModules() as $enabledModuleName) {
				$deps = self::getRequiredModuleResources($enabledModuleName);
				foreach ($deps as $depResource) {
					if (!$depResource->resourceValid()) {
						$done = false;
						self::setModuleEnabled($enabledModuleName, false, false, false);
						trigger_error('disabling module to prevent invalid module configuration for ' . $enabledModuleName . ' with required module ' . $dep, E_USER_WARNING);
						break;
					}
				}
			}
			if ($done) {
				break;
			}
		}
		self::getEnabledModulesOrderedByDependency(true);
	}

	/**
	 * @internal
	 */
	public static function setModuleStates($moduleStates)
	{
		foreach ($moduleStates as $module => $moduleState) {
			if (in_array($module, self::getAvailableModules()) && in_array($moduleState, array('enable', 'disable'))) {
				if ($moduleState == 'enable') {
					self::enableModule($module);
				} else {
					self::disableModule($module);
				}
			}
		}
		AutoLoader::reset();
	}

	/**
	 * enable a module
	 *
	 * @param string $module
	 * 
	 * @return boolean
	 */
	public static function enableModule($module, $updateClassCache = false)
	{
		return self::setModuleEnabled($module, true, true, $updateClassCache);
	}

	/**
	 * disbale a module
	 *
	 * @param string $module
	 * 
	 * @return boolean
	 */
	public static function disableModule($module, $updateClassCache = false)
	{
		return self::setModuleEnabled($module, false, true, $updateClassCache);
	}

	/**
	 * do it, do it now
	 *
	 * @param string $module
	 * @param boolean $enabled
	 * @return boolean
	 */
	private static function setModuleEnabled($module, $enabled, $checkConfig = true, $updateClassCache = true)
	{
		self::checkModuleAvailabilty($module);
		$currentConf = self::loadModuleConfiguration();
		if ($enabled) {
			if (!in_array($module, $currentConf->enabledModules)) {
				$currentConf->enabledModules[] = $module;
			}
		} else {
			if (in_array($module, $currentConf->enabledModules)) {
				$new = array();
				foreach ($currentConf->enabledModules as $oldModule) {
					if ($oldModule != $module) {
						$new[] = $oldModule;
					}
				}
				$currentConf->enabledModules = $new;
			}
		}
		$availableAndEnabled = array();
		$availableModules = self::getAvailableModules();
		foreach ($currentConf->enabledModules as $enabledModule) {
			if (in_array($enabledModule, $availableModules)) {
				$availableAndEnabled[] = $enabledModule;
			}
		}
		$currentConf->enabledModules = $availableAndEnabled;
		self::saveModuleConfiguration($currentConf);
		self::checkModuleConfig();
		Config::resetCache();
		if ($updateClassCache) {
			AutoLoader::reset();
		}
	}

	private static function checkModuleAvailabilty($module)
	{
		if (!in_array($module, self::getAvailableModules())) {
			trigger_error('can not find unavailable module ' . $module, E_USER_ERROR);
		}
	}

	/**
	 * ask if the module with the given name is enabled
	 * 
	 * @param string $moduleName
	 * 
	 * @return boolean
	 */
	public static function isModuleEnabled($moduleName)
	{
		return in_array($moduleName, self::getEnabledModules());
	}

	/**
	 * ask if the module with the given name is enabled
	 *
	 * @param string $moduleName
	 * 
	 * @return boolean
	 */
	public static function isModuleAvailable($moduleName)
	{
		return in_array($moduleName, self::getAvailableModules());
	}

	public static function getEnabledModules()
	{
		$conf = self::loadModuleConfiguration();
		$ret = self::loadModuleConfiguration()->enabledModules;
		$ret[] = \Foomo\Module::NAME;
		$ret = array_unique($ret);
		sort($ret);
		return $ret;
	}

	/**
	 *
	 * find out to which module a class belongs to
	 *
	 * @param string $className name of the class
	 *
	 * @return string name od the corresponding module
	 */
	public static function getClassModule($className)
	{
		$classFileName = AutoLoader::getClassFileName($className);
		if ($classFileName) {
			$enabledModules = self::getEnabledModules();
			//Timer::addMarker('got enabled modules');
			foreach ($enabledModules as $enabledModuleName) {
				$moduleLibPath = \Foomo\CORE_CONFIG_DIR_MODULES . DIRECTORY_SEPARATOR . $enabledModuleName . DIRECTORY_SEPARATOR . 'lib';
				if (strpos($classFileName, $moduleLibPath) === 0) {
					return $enabledModuleName;
				}
			}
			$coreLibPath = \Foomo\ROOT . DIRECTORY_SEPARATOR . 'lib';
			if (strpos($classFileName, $coreLibPath) === 0) {
				return \Foomo\Module::NAME;
			}
		} else {
			trigger_error('the class is not in the scope of the autoloader ' . $className . ' can not ', E_USER_WARNING);
		}
	}

	/**
	 * get lib folders
	 * 
	 * @internal
	 * 
	 * @return string[] folder names
	 */
	public static function getLibFolders()
	{
		$base = array();
		foreach (self::getEnabledModules() as $module) {
			$base = array_merge($base, self::getModuleLibFolders($module));
		}
		return $base;
	}
	/**
	 * get module lib folders
	 * 
	 * @param type $module
	 * 
	 * @internal
	 * 
	 * @return string[] folder names
	 */
	public static function getModuleLibFolders($module)
	{
		$testMode = Config::getMode() == Config::MODE_TEST;
		$folders = array();
		// @todo this is a hidden module dependency and not exactly clean ...
		if ($testMode && in_array('Foomo.TestRunner', self::getEnabledModules())) {
			$testsFolder = \Foomo\CORE_CONFIG_DIR_MODULES . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . 'tests';
			if (is_dir($testsFolder)) {
				$folders[] = $testsFolder;
			}
		}
		$libFolder = \Foomo\CORE_CONFIG_DIR_MODULES . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . 'lib';
		if (file_exists($libFolder)) {
			$folders[] = $libFolder;
		}
		return $folders;
	}

	/**
	 * dry name resloving
	 *
	 * @param string $moduleName name of the module
	 * 
	 * @return string
	 */
	private static function getModuleClassByName($moduleName)
	{
		return implode('\\', explode('.', $moduleName)) . '\\Module';
	}

	/**
	 * get the description of a module
	 *
	 * @param string $module name of the module
	 * 
	 * @return string
	 */
	public static function getModuleDescription($module)
	{
		if (self::tryLoadModuleClass($module)) {
			return call_user_func(array(self::getModuleClassByName($module), 'getDescription'));
		} else {
			return 'module class not available';
		}
	}

	/**
	 * try to load a module class
	 *
	 * @param string $moduleName name of the module
	 * 
	 * @return boolean
	 */
	private static function tryLoadModuleClass($moduleName)
	{
		$moduleClassName = self::getModuleClassByName($moduleName);
		if ($moduleName != \Foomo\Module::NAME && !class_exists($moduleClassName, false)) {
			include_once(self::getModuleClassFileName($moduleName));
		}
		return class_exists($moduleClassName, false);
	}

	private static function getModuleClassFileName($module)
	{
		$ret = \Foomo\CORE_CONFIG_DIR_MODULES . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR;
		return
			$ret .
			'lib' . DIRECTORY_SEPARATOR .
			\str_replace('\\', \DIRECTORY_SEPARATOR, self::getModuleClassByName($module)) . '.php'
		;
	}

	/**
	 * initialize all enabled modules
	 * 
	 * @internal
	 */
	public static function initializeModules()
	{
		Timer::addMarker(__METHOD__);
		$ordered = self::getEnabledModulesOrderedByDependency();
		Timer::addMarker('got ordered modules');
		$moduleLibDirs = array(\Foomo\ROOT . \DIRECTORY_SEPARATOR . 'lib');
		foreach ($ordered as $enabledModuleName) {
			$moduleLibDirs[] = \Foomo\CORE_CONFIG_DIR_MODULES . \DIRECTORY_SEPARATOR . $enabledModuleName . \DIRECTORY_SEPARATOR . 'lib';
			$moduleLibDirs[] = \Foomo\CORE_CONFIG_DIR_MODULES . \DIRECTORY_SEPARATOR . $enabledModuleName . \DIRECTORY_SEPARATOR . 'tests';
			include_once self::getModuleClassFileName($enabledModuleName);
			if (!class_exists(self::getModuleClassByName($enabledModuleName))) {
				trigger_error('can not initialize invalid module ' . self::getModuleClassByName($enabledModuleName));
			} else {
				$moduleIncludePaths = call_user_func(array(self::getModuleClassByName($enabledModuleName), 'getIncludePaths'));
			}
			if(!empty($moduleIncludePaths)) {
				$moduleLibDirs = array_merge($moduleLibDirs, $moduleIncludePaths);
			}
		}
		Utils::addIncludePaths(array_unique($moduleLibDirs));
		foreach ($ordered as $enabledModuleName) {
			call_user_func(array(self::getModuleClassByName($enabledModuleName), 'initializeModule'));
		}
	}

	private static function getEnabledModulesOrderedByDependency($forceUpdate = false)
	{
		if ($forceUpdate) {
			CacheManager::reset(__CLASS__.'::cachedGetEnabledModulesOrderedByDependency', false);
		}
		return CacheProxy::call(__CLASS__, 'cachedGetEnabledModulesOrderedByDependency');
	}

	/**
	 * Get modules in an order, that ensures accident free initialization - i.e. no dependencies are neglected
	 * 
	 * @Foomo\Cache\CacheResourceDescription
	 * 
	 * @return array
	 */
	public static function cachedGetEnabledModulesOrderedByDependency()
	{
		$ordered = array(\Foomo\Module::NAME);
		$enabledModules = self::getEnabledModules();
		$deps = array();
		foreach ($enabledModules as $enabledModuleName) {
			if($enabledModuleName == \Foomo\Module::NAME) {
				continue;
			}
			$deps[$enabledModuleName] = self::getRequiredModules($enabledModuleName);
			// there is always mama except for mama
			if($enabledModuleName != \Foomo\Module::NAME && !in_array(\Foomo\Module::NAME, $deps[$enabledModuleName])) {
				$deps[$enabledModuleName][] = \Foomo\Module::NAME;
			}
		}
		$lastModule = null;
		$currentModule = $enabledModules[0];
		// as long as we have havent ordered all modules
		$lastOrdered = array();
		while (count($enabledModules) > count($ordered)) {
			if($lastOrdered == $ordered) {
				trigger_error(
					'I AM stuck with module dependencies could resolve deps for: ' . 
					implode(', ', $ordered) . ' of: ' . 
					implode(', ', $enabledModules), 
					E_USER_ERROR
				);
			} else {
				$lastOrdered = $ordered;
			}
			foreach($deps as $enabledModuleName => $requiredModules) {
				if(in_array($enabledModuleName, $ordered)) {
					continue;
				} else {
					if(self::depsFulfilledForModule($requiredModules, $ordered)) {
						$ordered[] = $enabledModuleName;
					}
				}
			}
		}
		return $ordered;
	}
	private static function depsFulfilledForModule($requiredModules, $orderedModules)
	{
			// loop through all modules and there deps
			foreach ($requiredModules as $requiredModuleName) {
				if (!in_array($requiredModuleName, $orderedModules)) {
					return false;
				}
			}
			return true;
		
	}
	public static function getRequiredModuleResources($module)
	{
		if (self::tryLoadModuleClass($module)) {
			//return call_user_func(array(self::getModuleClassByName($module), 'getRequiredModules'));
			$allResources = self::getModuleResources($module);
			$moduleResources = array();
			foreach($allResources as $moduleResource) {
				if($moduleResource instanceof Resource\Module) {
					$moduleResources[] = $moduleResource;
				}
			}
			return $moduleResources;
		} else {
			return array();
		}
	}
	/**
	 * get a list a list of modules, a module depends upon to run
	 *
	 * @return string[]
	 */
	public static function getRequiredModules($module)
	{
		$ret = array();
		foreach(self::getRequiredModuleResources($module) as $moduleResource) {
			$ret[] = $moduleResource->name;// . ' => ' . $moduleResource->version;
		}
		return $ret;
	}

	/**
	 * get the module status
	 *
	 * @param string $module name of the module
	 * 
	 * @return string one of self::MODULE_STATUS_...
	 */
	public static function getModuleStatus($module)
	{
		$moduleClassName = self::getModuleClassByName($module);
		$ret = self::MODULE_STATUS_OK;
		if (self::tryLoadModuleClass($module)) {
			$required = self::getRequiredModules($module);
			$enabled = self::getEnabledModules();
			foreach ($required as $req) {
				if (!in_array($req, $enabled)) {
					$ret = self::MODULE_STATUS_REQUIRED_MODULES_MISSING;
					break;
				}
			}
			if ($ret == self::MODULE_STATUS_OK) {
				foreach (self::getModuleResources($module) as $moduleResource) {
					if (!call_user_func(array($moduleResource, 'resourceValid'))) {
						$ret = self::MODULE_STATUS_RESOURCES_INVALID;
						break;
					}
				}
			}
		} else {
			$ret = self::MODULE_STATUS_INVALID; //trigger_error($module . ' is not valid', E_USER_WARNING);
		}
		return $ret;
	}
	/**
	 * try to create module resources
	 * 
	 * @param type $module
	 * 
	 * @return string 
	 */
	public static function tryCreateModuleResources($module)
	{
		$resources = self::getModuleResources($module);
		$ret = '';
		foreach ($resources as $resource) {
			$ret .= get_class($resource) . ' :';
			if ($resource->resourceValid()) {
				$ret .= ' is valid: ' . $resource->resourceStatus();
			} else {
				$ret .= $resource->tryCreate();
			}
			$ret .= PHP_EOL;
		}
		return $ret;
	}

	/**
	 *
	 * @param type $module
	 * 
	 * @return Foomo\Module\Resource[] 
	 */
	public static function getModuleResources($module)
	{
		// handle not enabled modules carefully
		if(self::tryLoadModuleClass($module)) {
			$moduleClassName = self::getModuleClassByName($module);
			/*
			if(!class_exists($moduleClassName)) {
				// ok try load it
				$moduleClassFile = \Foomo\CORE_CONFIG_DIR_MODULES . \DIRECTORY_SEPARATOR . $module . \DIRECTORY_SEPARATOR . 'lib' . \DIRECTORY_SEPARATOR . implode(\DIRECTORY_SEPARATOR, explode('\\', $moduleClassName)) . '.php';
				if(file_exists($moduleClassFile)) {
					include_once $moduleClassFile;
					var_dump($moduleClassFile, class_exists($moduleClassName, false));
				}
			}
			 */
			return array_merge(
				call_user_func(array($moduleClassName, 'getResources')), array(
					Resource\Fs::getAbsoluteResource(Resource\FS::TYPE_FOLDER, \Foomo\Config::getLogDir($module))
				)
			);
		}
	}

	public static function getModuleStatusReport($module)
	{
		$report = '';
		foreach (self::getModuleResources($module) as $res) {
			$report .= call_user_func(array($res, 'resourceStatus')) . PHP_EOL;
		}
		return $report;
	}

	/**
	 * loads the current configuration
	 *
	 * @return Foomo\Core\DomainConfig
	 */
	private static function loadModuleConfiguration()
	{
		$currentConf = Config::getConf(\Foomo\Module::NAME, DomainConfig::NAME);
		if (is_null($currentConf)) {
			Config::restoreConfDefault(\Foomo\Module::NAME, DomainConfig::NAME);
			$currentConf = Config::getConf(\Foomo\Module::NAME, DomainConfig::NAME);
		}
		return $currentConf;
	}

	/**
	 * saves the current configuration
	 * 
	 * @return boolean
	 */
	private static function saveModuleConfiguration(DomainConfig $conf)
	{
		//$ret = Config::setConf($conf, DomainConfig::NAME) && self::writeServerConfs($conf);
		return Config::setConf($conf, \Foomo\Module::NAME) && self::updateSymlinksForHtdocs();
	}
	
	/**
	 * update symlinks for module htdocs
	 * 
	 * @internal
	 * 
	 * @return boolean
	 */
	public static function updateSymlinksForHtdocs()
	{
		$symlinkBaseFolder = Config::getVarDir() . DIRECTORY_SEPARATOR . 'htdocs' . DIRECTORY_SEPARATOR . 'modules';
		$existing = array();
		$iterator = new DirectoryIterator($symlinkBaseFolder);
		$enabledModules = self::getEnabledModules();
		foreach ($iterator as $file) {
			/* @var $file \SplFileInfo */
			// cleanup
			if (is_link($file->getPathname()) && !in_array($file->getBasename(), $enabledModules)) {
				\unlink($file->getPathname());
			}
		}
		foreach ($enabledModules as $enabled) {
			if (!in_array($enabled, $existing)) {
				$symlinkFilename = $symlinkBaseFolder . \DIRECTORY_SEPARATOR . $enabled;
				$targetFilename = \Foomo\CORE_CONFIG_DIR_MODULES . \DIRECTORY_SEPARATOR . $enabled . \DIRECTORY_SEPARATOR . 'htdocs';
				if (!\file_exists($symlinkFilename)) {
					\symlink($targetFilename, $symlinkFilename);
				}
			}
		}
		return true;
	}

	/**
	 * find out if the model has a front end, that can be reached wit a browser
	 *
	 * @param string $module name of the browser
	 * 
	 * @return boolean
	 */
	public static function moduleHasFrontend($module)
	{
		if (file_exists(\Foomo\CORE_CONFIG_DIR_MODULES . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . 'htdocs' . DIRECTORY_SEPARATOR . 'index.php')) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * find out, if there is a MVC app for the module
	 * 
	 * @param string $module
	 * @return boolean
	 */
	public static function moduleHasMVCFrontend($module)
	{
		return !is_null(self::getModuleMVCFrontEndClassName($module));
	}

	/**
	 * get a module frontend MVC app
	 * 
	 * @param string $module name of the module
	 * @return string name of the module mvc app class
	 */
	public static function getModuleMVCFrontEndClassName($module)
	{
		$classMap = AutoLoader::getClassMap();
		// @todo jan: This seems to be unnecessary! 
		AutoLoader::getClassMap();
		$libFolder = \Foomo\CORE_CONFIG_DIR_MODULES . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . 'lib';
		foreach ($classMap as $className => $classFileName) {
			if (strpos($classFileName, $libFolder) === 0 && class_exists($className)) {
				$refl = new ReflectionClass($className);
				if ($refl->implementsInterface('Foomo\\Modules\\ModuleAppInterface') && !$refl->isAbstract()) {
					return $refl->getName();
				}
			}
		}
		return null;
	}
}
