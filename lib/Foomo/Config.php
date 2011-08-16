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

namespace Foomo;

use ReflectionClass;
use Foomo\Config\AbstractConfig;
use InvalidArgumentException;

/**
 * manages the application runmode and configuration
 *
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
class Config
{
	//---------------------------------------------------------------------------------------------
	// ~ Constants
	//---------------------------------------------------------------------------------------------

	const MODE_TEST			= 'test';
	const CACHE_PATH		= 'config';
	const MODE_PRODUCTION	= 'production';
	const MODE_DEVELOPMENT	= 'development';

	//---------------------------------------------------------------------------------------------
	// ~ Static variables
	//---------------------------------------------------------------------------------------------

	/**
	 * thats where we keep the config
	 *
	 * @var string
	 */
	private static $currentMode = null;
	/**
	 * a runtime cache, that ensures, that you will get only one instance of a
	 * cached conf when calling getConf() with the same values
	 *
	 * @var array
	 */
	private static $confCache = array();
	//---------------------------------------------------------------------------------------------
	// ~ Public static methods
	//---------------------------------------------------------------------------------------------

	/**
	 * reads the current run mode setting
	 * @access internal
	 */
	public static function init()
	{
		if (isset($_SERVER['FOOMO_RUN_MODE']) && in_array($_SERVER['FOOMO_RUN_MODE'], array(self::MODE_PRODUCTION, self::MODE_DEVELOPMENT, self::MODE_TEST))) {
			self::$currentMode = $_SERVER['FOOMO_RUN_MODE'];
		} else {
			throw new Config\Exception(Config\Exception::MESSAGE_RUN_MODE_NOT_SET, Config\Exception::CODE_RUN_MODE_NOT_SET);
		}
	}

	/**
	 * does a config for a module exist or not
	 *
	 * @param string $module name of the module, you want to configure
	 * @param string $name name of the config
	 * @param string $domain you need multiple for a domain in a module - here you are
	 * @return boolean
	 */
	public static function confExists($module, $name, $domain = '')
	{
		return file_exists(self::getConfFileName($module, $name, $domain));
	}

	/**
	 * restore a configuration default - will create a new one, if not present
	 *
	 * @param string $module
	 * @param string $name
	 * @param string $domain
	 *
	 * @return boolean
	 */
	public static function restoreConfDefault($module, $name, $domain = '')
	{
		$confClassName = self::getDomainConfigClassName($name);
		$conf = new $confClassName(true);
		return self::setConf($conf, $module, $domain);
	}

	/**
	 * @param string $name
	 * @return string
	 */
	public static function getDomainConfigClassName($name)
	{
		// the core bootstrap workaround:
		if ($name == 'Foomo.core') return 'Foomo\\Core\\DomainConfig';
		$classMap = AutoLoader::getClassMap();
		$confClassName = null;
		foreach ($classMap as $className => $classFileName) {
			if(class_exists($className)) {
				$refl = new ReflectionClass($className);
				if ($refl->isSubclassOf('Foomo\\Config\\AbstractConfig') && !$refl->isAbstract()) {
					if (constant($refl->getName() . '::NAME') == $name) {
						$confClassName = $refl->getName();
						break;
					}
				}
			}
		}
		if (!$confClassName) throw new InvalidArgumentException('unknown domain configuration : ' . $name, 1);
		return $confClassName;
	}

	/**
	 * load a configuration for a domain
	 *
	 * @param string $module name of the module, you want to configure
	 * @param string $name the domain of configuration like db, mail, YOU name it
	 * @param string $domain you need multiple for a domain in a module - here you are
	 *
	 * @return Foomo\Config\AbstractConfig
	 */
	public static function getConf($module, $name, $domain='')
	{
		$cacheKey = $module.$name.$domain;
		if(!isset(self::$confCache[$cacheKey])) {
			self::$confCache[$cacheKey] = \Foomo\Cache\Proxy::call(__CLASS__, 'cachedGetConf', array($module, $name, $domain));
		}
		return self::$confCache[$cacheKey];
	}

	/**
	 * load configurations for a domain for all enabled modules
	 *
	 * @param string $name the domain of configuration like db, mail, YOU name it
	 * @param string $domain you need multiple for a domain in a module - here you are
	 * @return Foomo\Config\AbstractConfig[]
	 */
	public static function getConfs($name, $domain='')
	{
		$configs = array();
		$modules = Modules\Manager::getEnabledModules();
		foreach ($modules as $module) if (null != $config = self::getConf($module, $name, $domain)) $configs[] = $config;
		return $configs;
	}

	/**
	 * @param type $name
	 * @return AbstractConfig
	 */
	public static function getDefaultConfig($name)
	{
		$confClassName = self::getDomainConfigClassName($name);
		$conf = new $confClassName(true);
		return $conf;
	}

	/**
	 * what the user did and not what the yaml parser crippled out of it
	 *
	 * @param string $module
	 * @param string $name
	 * @param string $domain
	 * @return string
	 */
	public static function getConfOriginalYaml($module, $name, $domain = '')
	{
		$filename = self::getConfFileName($module, $name, $domain);
		if (file_exists($filename)) {
			return \file_get_contents($filename);
		}
	}

	/**
	 * set a conf
	 *
	 * @param AbstractConfig $conf the conf itself
	 * @param string $module name of the module
	 * @param string $domain use this if you have several of a kind
	 * @return boolean
	 */
	public static function setConf(AbstractConfig $conf, $module, $domain = '', $originalYaml = '')
	{
		$confFile = self::getConfFileNameByConf($conf, $module, $domain);
		// make a backup of the current conf
		if (\file_exists($confFile)) {
			$oldConf = \file_get_contents($confFile);
			\file_put_contents($confFile . '-old-' . date('Y-m-d-H-i-s'), $oldConf);
		}
		if (!is_dir(dirname($confFile))) {
			// trigger_error('creating conf dir for module ' . $module . ' and subDomain ' . $domain);
			if (!empty($domain)) {
				$moduleConfDir = dirname(dirname($confFile));
				if (!is_dir($moduleConfDir)) {
					mkdir($moduleConfDir);
				}
				mkdir(dirname($confFile));
			} else {
				mkdir(dirname($confFile));
			}
		}
		if (file_put_contents($confFile, $originalYaml ? $originalYaml : \Foomo\Yaml::dump($conf->getValue()))) {
			$conf->saved();
			self::resetCache();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * get the current config YAML
	 *
	 * @param string $module
	 * @param string $name
	 * @param string $domain
	 * @return string
	 */
	public static function getCurrentConfYAML($module, $name, $domain = '')
	{
		$file = self::getConfFileName($module, $name, $domain);
		if (file_exists($file)) {
			return file_get_contents($file);
		} else {
			return '';
		}
	}

	/**
	 * remove a configuration
	 *
	 * @param string $module
	 * @param string $name
	 * @param string $domain
	 */
	public static function removeConf($module, $name, $domain = '')
	{
		if (self::confExists($module, $name, $domain)) {
			$confFilename = self::getConfFileName($module, $name, $domain);
			\rename($confFilename, $confFilename . '-deleted-' . date('Y-m-d_H-i-s'));
			self::resetCache();
		}
	}

	/**
	 * deprecated hack to invalidate the config cache
	 *
	 * @deprecated
	 * @internal
	 */
	public static function resetCache()
	{
		self::$confCache = array();
		\Foomo\Cache\Manager::reset(__CLASS__.'::cachedGetConf', false);
		//\Foomo\Cache\Manager::invalidateWithQuery(__CLASS__ . '::cachedGetConf', null, true, \Foomo\Cache\Invalidator::POLICY_DELETE);
	}

	/**
	 * get the current mode
	 *
	 * @return string
	 */
	public static function getMode()
	{
		return self::$currentMode;
	}

	/**
	 * get the directory where all the configs go to - this is rails inspired
	 *
	 * @return string
	 */
	public static function getConfigDir()
	{
		$ret = \Foomo\CORE_CONFIG_DIR_CONFIG . DIRECTORY_SEPARATOR . self::$currentMode;
		self::validatePath($ret);
		return $ret;
	}

	/**
	 * path to the modules dir
	 *
	 * @param string $module
	 * @return string
	 */
	public static function getModuleDir($module='')
	{
		$ret = \Foomo\CORE_CONFIG_DIR_MODULES;
		if ($module != '') $ret .= DIRECTORY_SEPARATOR . $module;
		if (!file_exists($ret)) throw new \Exception('Module path ' . $ret . ' does not exist');
		return $ret;
	}

	/**
	 * get the directory where all the data go to - this is unix inspired
	 *
	 * @return string
	 */
	public static function getVarDir($module='')
	{
		$ret = \Foomo\CORE_CONFIG_DIR_VAR . DIRECTORY_SEPARATOR . self::$currentMode;
		if ($module != '') $ret .= DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $module;
		self::validatePath($ret);
		return $ret;
	}

	/**
	 * get the current cache directory
	 *
	 * @return string
	 */
	public static function getCacheDir($module='')
	{
		$ret = self::getVarDir() . DIRECTORY_SEPARATOR . 'cache';
		if ($module != '') $ret .= DIRECTORY_SEPARATOR . $module;
		self::validatePath($ret);
		return $ret;
	}

	/**
	 * get a directory in the dynamic htdocs
	 *
	 * @param string $module
	 * @return string
	 */
	public static function getHtdocsDir($module)
	{
		return \Foomo\CORE_CONFIG_DIR_MODULES . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . 'htdocs';
	}

	/**
	 * get a directory in the dynamic htdocs
	 *
	 * @param string $module
	 * @return string
	 */
	public static function getHtdocsVarDir($module='')
	{
		$ret = self::getVarDir() . DIRECTORY_SEPARATOR . 'htdocs' . DIRECTORY_SEPARATOR . 'modulesVar';
		if ($module != '') $ret .= DIRECTORY_SEPARATOR . $module;
		self::validatePath($ret);
		return $ret;
	}

	/**
	 * get a directory in the dynamic htdocs
	 *
	 * @param string $module
	 * @return string
	 */
	public static function getHtdocsPath($module)
	{
		return \Foomo\ROOT_HTTP . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $module;
	}

	/**
	 * get a directory in the dynamic htdocs
	 *
	 * @param string $module
	 * @return string
	 */
	public static function getHtdocsVarPath($module='')
	{
		$ret = \Foomo\ROOT_HTTP . DIRECTORY_SEPARATOR . 'modulesVar';
		if ($module != '') $ret .= DIRECTORY_SEPARATOR . $module;
		return $ret;
	}

	/**
	 * get your log directory
	 *
	 * @param string $module
	 * @return string
	 */
	public static function getLogDir($module='')
	{
		$ret = self::getVarDir() . DIRECTORY_SEPARATOR . 'logs';
		if ($module != '') $ret .= DIRECTORY_SEPARATOR . $module;
		self::validatePath($ret);
		return $ret;
	}

	/**
	 * get the temp directory
	 *
	 * @return string
	 */
	public static function getTempDir($module='')
	{
		$ret = self::getVarDir() . DIRECTORY_SEPARATOR . 'tmp';
		if ($module != '') $ret .= DIRECTORY_SEPARATOR . $module;
		self::validatePath($ret);
		return $ret;
	}

	//---------------------------------------------------------------------------------------------
	// ~ Cached methods
	//---------------------------------------------------------------------------------------------

	/**
	 * @Foomo\Cache\CacheResourceDescription()
	 *
	 * @internal
	 * @param string $module
	 * @param string $name
	 * @param string $domain
	 * @return Foomo\Config\AbstractConfig $domain
	 */
	public static function cachedGetConf($module, $name, $domain)
	{
		$conf = self::getDefaultConfig($name);
		$confYaml = @file_get_contents(self::getConfFileName($module, $name, $domain));
		if ($confYaml !== false) {
			$conf->setValue(\Foomo\Yaml::parse($confYaml));
			return $conf;
		} else {
			// trigger_error('requested configuration for module ' . $module . ' does not exist in domain => creating default for domain ' . $name . ' in subDomain ' . $domain, E_USER_NOTICE);
			// self::setConf($conf, $module, $domain);
			return null;
		}
	}

	//---------------------------------------------------------------------------------------------
	// ~ Private static methods
	//---------------------------------------------------------------------------------------------

	/**
	 * get the name for a conf file
	 *
	 * @param string $module name of the module
	 * @param mixed $conf
	 * @return string
	 */
	private static function getConfFileNameByConf(AbstractConfig $conf, $module, $domain = '')
	{
		return self::getConfFileName($module, $conf->getName(), $domain);
	}

	/**
	 * get filename for a config
	 *
	 * @param string $module
	 * @param string $name
	 * @param string $domain
	 *
	 * @return type
	 */
	private static function getConfFileName($module, $name, $domain)
	{
		$confFile = self::getConfigDir() . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $module;
		if (!empty($domain)) {
			$domain = DIRECTORY_SEPARATOR . $domain . DIRECTORY_SEPARATOR;
		}
		return $confFile . DIRECTORY_SEPARATOR . $domain . $name . '.yml';
	}

	/**
	 * @param string $filename
	 */
	private static function validatePath($pathname)
	{
		if (file_exists($pathname)) return true;
		\Foomo\Modules\Resource\Fs::getAbsoluteResource(\Foomo\Modules\Resource\Fs::TYPE_FOLDER, $pathname)->tryCreate();
		if (!\file_exists($pathname)) throw new \Exception('Resource ' . $pathname . ' does not exits and could not be created! ' . $msg);
	}
}