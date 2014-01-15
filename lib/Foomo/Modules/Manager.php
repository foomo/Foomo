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

namespace Foomo\Modules;

use DirectoryIterator;
use Foomo\MVC;
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
 *
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
class Manager
{
	//---------------------------------------------------------------------------------------------
	// ~ Constants
	//---------------------------------------------------------------------------------------------

	const MODULE_STATUS_OK							= 'MODULE_STATUS_OK';
	const MODULE_STATUS_INVALID						= 'MODULE_STATUS_INVALID';
	const MODULE_STATUS_RESOURCES_INVALID			= 'MODULE_STATUS_RESOURCES_INVALID';
	const MODULE_STATUS_REQUIRED_MODULES_MISSING	= 'MODULE_STATUS_REQUIRED_MODULES_MISSING';

	const MAKE_LOCK_NAME                            = 'foomoMakeLock';

	//---------------------------------------------------------------------------------------------
	// ~ Public static methods
	//---------------------------------------------------------------------------------------------
	/**
	 * get the name of the module on whichs htdocs the $_SERVER['DOCUMENT_ROOT'] variable is pointing to
	 *
	 * @internal
	 * @return string
	 */
	public static function getDocumentRootModule()
	{
		if(!empty($_SERVER['DOCUMENT_ROOT'])) {
			$realDocRoot = realpath($_SERVER['DOCUMENT_ROOT']);
			foreach(self::getEnabledModules() as $enabledModuleName) {
				$moduleHTDocs = \Foomo\CORE_CONFIG_DIR_MODULES . DIRECTORY_SEPARATOR . $enabledModuleName . DIRECTORY_SEPARATOR . 'htdocs';
				if(file_exists($moduleHTDocs) && is_dir($moduleHTDocs) && $realDocRoot == realpath($moduleHTDocs)) {
					return $enabledModuleName;
				}
			}
		}
	}

	/**
	 * @internal
	 * @param string $module
	 * @return bool
	 */
	public static function moduleCanBeEnabled($module)
	{
		$availableModules = self::getAvailableModules();
		foreach(self::getRequiredModulesRecursively($module) as $depModule) {
			if(!in_array($depModule, $availableModules)) {
				return false;
			}
		}
		return true;
	}
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

	/**
	 * get module version
	 *
	 * @param string $module
	 * @return string
	 */
	public static function getModuleVersion($module)
	{
		if (in_array($module, self::getAvailableModules())) {
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
			$done = true;
			foreach (self::getEnabledModules() as $enabledModuleName) {
				$deps = self::getRequiredModuleResources($enabledModuleName);
				foreach ($deps as $depResource) {
					/* @var $depResource \Foomo\Modules\Resource\Module */
					if (!$depResource->resourceValid()) {
						$done = false;
						self::setModuleEnabled($enabledModuleName, false, false);
						trigger_error('disabling module to prevent invalid module configuration for ' . $enabledModuleName . ' with required module ' . $depResource->name, E_USER_WARNING);
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
		AutoLoader::resetCache();
	}

	/**
	 * @param string $module
	 * @return boolean
	 */
	public static function isEnabled($module)
	{
		return in_array($module, self::getEnabledModules());
	}

	/**
	 * enable a module
	 *
	 * @param string $module
	 * @param bool $updateClassCache
	 * @return boolean
	 */
	public static function enableModule($module, $updateClassCache = false)
	{
		return self::setModuleEnabled($module, true, $updateClassCache);
	}

	/**
	 * disable all except mama
	 * @internal
	 */
	public static function disableAllModules()
	{
		return self::setEnabledModules(array(\Foomo\Module::NAME), true);
	}
	/**
	 * disable a module
	 *
	 * @param string $module
	 * @param bool $updateClassCache
	 * @return boolean
	 */
	public static function disableModule($module, $updateClassCache = false)
	{
		return self::setModuleEnabled($module, false, $updateClassCache);
	}

	/**
	 * ask if the module with the given name is enabled
	 *
	 * @param string $moduleName
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
	 * @return boolean
	 */
	public static function isModuleAvailable($moduleName)
	{
		return in_array($moduleName, self::getAvailableModules());
	}

	/**
	 * @return string[]
	 */
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
	 * @return string name od the corresponding module
	 */
	public static function getClassModule($className)
	{
		$classFileName = AutoLoader::getClassFileName($className);
		if ($classFileName) {
			$enabledModules = self::getEnabledModules();
			foreach ($enabledModules as $enabledModuleName) {
				foreach(self::getModuleLibFolders($enabledModuleName) as $moduleLibPath) {
					if (strpos($classFileName, $moduleLibPath) === 0) {
						return $enabledModuleName;
					}
				}
			}
		} else {
			trigger_error('the class is not in the scope of the autoloader ' . $className, E_USER_WARNING);
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
	 * @internal
	 * @param type $module
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
	 * @return string
	 */
	public static function getModuleClassByName($moduleName)
	{
		return implode('\\', explode('.', $moduleName)) . '\\Module';
	}

	/**
	 * get the description of a module
	 *
	 * @param string $module name of the module
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

	/**
	 * @param string $module
	 * @return \Foomo\Module
	 */
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
	 * @param string $module name of the module
	 * @return string[]
	 */
	public static function getRequiredModules($module)
	{
		$ret = array();
		foreach(self::getRequiredModuleResources($module) as $moduleResource) {
			$ret[] = $moduleResource->name;
		}
		return $ret;
	}


	private static function getRequiredModulesRecursively($module)
	{
		return self::flattenModuleDepencyTree(self::getRequiredModuleTree($module));
	}
	private static function flattenModuleDepencyTree($tree, &$flatList = array())
	{
		foreach($tree as $module => $depsOrModule) {
			if(is_array($depsOrModule)) {
				self::flattenModuleDepencyTree($depsOrModule, $flatList);
			} else {
				if(!in_array($depsOrModule->name, $flatList)) {
					$flatList[] = $depsOrModule->name;
				}
			}
		}
		return $flatList;
	}

	/**
	 * @param $module
	 * @param array $tree
	 * @return array
	 * @internal
	 */
	public static function getRequiredModuleTree($module, &$tree = array(), $coveredInThisTree = array())
	{
		foreach(self::getRequiredModuleResources($module) as $depModuleResource) {
			/* @var $depModuleResource \Foomo\Modules\Resource\Module */
			if(!in_array($depModuleResource->name, $coveredInThisTree)) {
				$coveredInThisTree[] = $depModuleResource->name;
				$tree[] = $depModuleResource;
				$tree[$depModuleResource->name] = array();
				self::getRequiredModuleTree($depModuleResource->name, $tree[$depModuleResource->name], $coveredInThisTree);
				if(empty($tree[$depModuleResource->name])) {
					unset($tree[$depModuleResource->name]);
				}
			}
		}
		return $tree;
	}

	/**
	 * get the module status
	 *
	 * @param string $module name of the module
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
	 * @param string $module
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

	public static function tryCreateModuleResourcesForAllEnabledModules()
	{
		$ret = '';
		foreach(self::getEnabledModules() as $enabledModule) {
			$ret .= 'module ' . $enabledModule . ':' . PHP_EOL . '---------------------------------------------------------------------------' . PHP_EOL;
			$ret .= self::tryCreateModuleResources($enabledModule);
			$ret .= PHP_EOL;
		}
		return $ret;
	}

	/**
	 * @param string $module
	 * @return \Foomo\Modules\Resource[]
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
			$resources = array_merge(
				call_user_func(array($moduleClassName, 'getResources')), array(
					Resource\Fs::getAbsoluteResource(Resource\FS::TYPE_FOLDER, \Foomo\Config::getLogDir($module))
				)
			);
			$validatedResources = array();
			foreach($resources as $resource) {
				if(is_object($resource) && ($resource instanceof Resource)) {
					$validatedResources[] = $resource;
				} else {
					trigger_error('invalid value returned as a module resource from ' . $moduleClassName . '::getResources() all resources must be instances of Foomo\Modules\Resource', E_USER_ERROR);
				}
			}
			return $validatedResources;
		}
	}

	/**
	 * @param type $module
	 * @return string
	 */
	public static function getModuleStatusReport($module)
	{
		$report = '';
		foreach (self::getModuleResources($module) as $res) {
			$report .= call_user_func(array($res, 'resourceStatus')) . PHP_EOL;
		}
		return $report;
	}

	/**
	 * update symlinks for module htdocs
	 *
	 * @internal
	 * @return boolean
	 */
	public static function updateSymlinksForHtdocs()
	{
		$baseDir = Config::getVarDir() . DIRECTORY_SEPARATOR . 'htdocs';
		// clean away all symlinks
		$symlinkBaseFolderModules = $baseDir . DIRECTORY_SEPARATOR . 'modules';
		$symlinkBaseFolderModulesVar = $baseDir . DIRECTORY_SEPARATOR . 'modulesVar';
		foreach(array($symlinkBaseFolderModules,  $symlinkBaseFolderModulesVar) as $baseFolder) {
			foreach (new DirectoryIterator($baseFolder) as $file) {
				/* @var $file \SplFileInfo */
				// cleanup
				if (is_link($file->getPathname())) {
					\unlink($file->getPathname());
				}
			}
		}
		$buildNumber = \Foomo\Module::getDomainConfig()->buildNumber;
		foreach (self::getEnabledModules() as $enabled) {
			// symlinks for htdocs
			//   where to link to
			$targetFilename = \Foomo\CORE_CONFIG_DIR_MODULES . \DIRECTORY_SEPARATOR . $enabled . \DIRECTORY_SEPARATOR . 'htdocs';
			$symlinkFilenames = array();
			//   not versioned
			$symlinkFilenames[] = $symlinkBaseFolderModules . \DIRECTORY_SEPARATOR . $enabled;
			//   versioned
			$symlinkFilenames[] = $symlinkFilenames[0] . '-' . $buildNumber;
			foreach($symlinkFilenames as $symlinkFilename) {
				self::symlinkModuleFolderIfExists($targetFilename, $symlinkFilename);
			}
			// symlinks for htdocs var
			$targetFilename = Config::getVarDir() . DIRECTORY_SEPARATOR . 'htdocs' . DIRECTORY_SEPARATOR . 'modulesVar' . DIRECTORY_SEPARATOR . $enabled;
			$symlinkFilename = $targetFilename . '-' . $buildNumber;
			self::symlinkModuleFolderIfExists($targetFilename, $symlinkFilename);
		}
		return true;
	}
	private static function symlinkModuleFolderIfExists($targetFilename, $symlinkFilename)
	{
		if(file_exists($targetFilename) && is_dir($targetFilename) && !file_exists($symlinkFilename)) {
			if(!symlink($targetFilename, $symlinkFilename)) {
				trigger_error('could not create symlink ' . $targetFilename . ' => ' . $symlinkFilename, E_USER_ERROR);
			}
		}
	}

	/**
	 * find out if the model has a front end, that can be reached wit a browser
	 *
	 * @param string $module name of the browser
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
		$libFolder = \Foomo\CORE_CONFIG_DIR_MODULES . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . 'lib';
		foreach ($classMap as $className => $classFileName) {
			if (strpos($classFileName, $libFolder) === 0 && class_exists($className)) {
				$refl = new ReflectionClass($className);
				if ($refl->implementsInterface('Foomo\\Modules\\ModuleAppInterface') && !$refl->isAbstract()) return $refl->getName();
			}
		}
		return null;
	}

	/**
	 * @param string $className
	 *
	 * @return string
	 */
	public static function getModuleByClassName($className)
	{
		$classFilename = AutoLoader::getClassFileName($className);
		foreach(self::getEnabledModules() as $enabledModule) {
			$moduleRoot = \Foomo\CORE_CONFIG_DIR_MODULES . DIRECTORY_SEPARATOR . $enabledModule . DIRECTORY_SEPARATOR;
			if (strpos($classFilename, $moduleRoot) === 0) return $enabledModule;
		}
	}

	//---------------------------------------------------------------------------------------------
	// ~ Public cached methods
	//---------------------------------------------------------------------------------------------

	/**
	 * Get modules in an order, that ensures accident free initialization - i.e. no dependencies are neglected
	 *
	 * @internal
	 *
	 * @Foomo\Cache\CacheResourceDescription
	 *
	 * @return string[]
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
		// as long as we have haven´t ordered all modules
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

	//---------------------------------------------------------------------------------------------
	// ~ Private static methods
	//---------------------------------------------------------------------------------------------

	/**
	 * do it, do it now
	 *
	 * @param string $module
	 * @param bool $enabled
	 * @param bool $updateClassCache
	 */
	private static function setModuleEnabled($module, $enabled, $updateClassCache = true)
	{
		self::checkModuleAvailabilty($module);
		$currentConf = self::loadModuleConfiguration();
		if ($enabled) {
			$recursiveDeps = self::getRequiredModulesRecursively($module);
			$recursiveDeps[] = $module;
			foreach($recursiveDeps as $module) {
				if (!in_array($module, $currentConf->enabledModules)) {
					$currentConf->enabledModules[] = $module;
				}
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
		self::setEnabledModules($availableAndEnabled, $updateClassCache);
	}
	private static function setEnabledModules(array $availableAndEnabled, $updateClassCache)
	{
		$currentConf = self::loadModuleConfiguration();
		$currentConf->enabledModules = $availableAndEnabled;
		self::saveModuleConfiguration($currentConf);
		self::checkModuleConfig();
		Config::resetCache();
		if ($updateClassCache) {
			AutoLoader::resetCache();
		}
	}

	/**
	 * @param string $module
	 */
	private static function checkModuleAvailabilty($module)
	{
		if (!in_array($module, self::getAvailableModules())) {
			trigger_error('can not find unavailable module ' . $module, E_USER_ERROR);
		}
	}

	/**
	 * loads the current configuration
	 *
	 * @return \Foomo\Core\DomainConfig
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
	 * @param DomainConfig $conf
	 *
	 * @return bool
	 */
	private static function saveModuleConfiguration(DomainConfig $conf)
	{
		//$ret = Config::setConf($conf, DomainConfig::NAME) && self::writeServerConfs($conf);
		return Config::setConf($conf, \Foomo\Module::NAME) && self::updateSymlinksForHtdocs();
	}

	/**
	 * @param string $requiredModules
	 * @param string $orderedModules
	 * @return boolean
	 */
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
	/**
	 * make sure module classes of not enabled modules are loaded
	 * @intenal
	 */
	public static function loadAvailableModuleClasses()
	{
		foreach(self::getAvailableModules() as $availableModuleName) {
			self::tryLoadModuleClass($availableModuleName);
		}
	}
	/**
	 * @param string $module
	 * @return string
	 */
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
	 * @param boolean $forceUpdate
	 * @return string[]
	 */
	private static function getEnabledModulesOrderedByDependency($forceUpdate=false)
	{
		if ($forceUpdate) CacheManager::reset(__CLASS__.'::cachedGetEnabledModulesOrderedByDependency', false);
		return CacheProxy::call(__CLASS__, 'cachedGetEnabledModulesOrderedByDependency');
	}

	/**
	 * make sth.
	 *
	 * @param string[] $targets
	 *
	 * @return array target => MakeResult[], target => MakeResult[], ...
	 */
	public static function make($targets)
	{
		\Foomo\Lock::lock(self::MAKE_LOCK_NAME, true);
		$targetResults = array();
		foreach($targets as $target) {
			$results = array();
			foreach(self::getEnabledModulesOrderedByDependency() as $enabledModule) {
				$result = new MakeResult($enabledModule);
				call_user_func_array(array(self::getModuleClassByName($enabledModule), 'make'), array($target, $result));
				$results[] = $result;
			}
			$targetResults[$target] = $results;
		}
		\Foomo\Lock::release(self::MAKE_LOCK_NAME);
		return $targetResults;
	}
	public static function makeIsRunning()
	{
		return \Foomo\Lock::isLocked(self::MAKE_LOCK_NAME);
	}

	/**
	 * call a hook on all modules
	 *
	 * @param string $hook
	 * @param array $args
	 *
	 * @internal
	 */
	public static function runModuleHook($hook, $args)
	{
		foreach(self::getEnabledModules() as $module) {
			call_user_func_array(array(self::getModuleClassByName($module), 'hook' . ucfirst($hook)), $args);
		}
	}
}
