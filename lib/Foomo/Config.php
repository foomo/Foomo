<?php

/*
 * bestbytes-copyright-placeholder
 */

namespace Foomo;

use ReflectionClass;
use Foomo\Config\AbstractConfig;
use InvalidArgumentException;

/**
 * manages the application runmode and configuration
 */
class Config {
	const CACHE_PATH = 'config';
	const MODE_DEVELOPMENT = 'development';
	const MODE_PRODUCTION = 'production';
	const MODE_TEST = 'test';
	/**
	 * thats where we keep the config
	 *
	 * @var string
	 */
	private static $currentMode = null;

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
	 *
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

	public static function getDomainConfigClassName($name)
	{
		// the core bootstrap workaround:
		if ($name == 'Foomo.core') {
			return 'Foomo\\Core\\DomainConfig';
		}
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
		if (!$confClassName) {
			throw new InvalidArgumentException('unknown domain configuration : ' . $name, 1);
		}
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
	public static function getConf($module, $name, $domain = '')
	{
		return \Foomo\Cache\Proxy::call(__CLASS__, 'cachedGetConf', array($module, $name, $domain));
	}

	/**
	 * @Foomo\Cache\CacheResourceDescription()
	 *
	 * @param string $module
	 * @param string $name
	 * @param string $domain
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

	/**
	 *
	 * @param type $name
	 * 
	 * @return AbstractConfig
	 */
	public static function getDefaultConfig($name)
	{
		$confClassName = self::getDomainConfigClassName($name);
		$conf = new $confClassName(true);
		return $conf;
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
	 *
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
	 * 
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
	 * deprecated hack to invalidate the config cache
	 * 
	 * @internal
	 */
	public static function resetCache()
	{
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
		return \Foomo\CORE_CONFIG_DIR_CONFIG . DIRECTORY_SEPARATOR . self::$currentMode;
	}

	/**
	 * get your log directory
	 *
	 * @param string $module
	 *
	 * @return string
	 */
	public static function getLogDir($module = '') // \Foomo\Module::NAME
	{
		return self::getVarDir() . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . $module;
	}

	/**
	 * get the directory where all the data go to - this is unix inspired
	 *
	 * @return string
	 */
	public static function getVarDir()
	{
		return \Foomo\CORE_CONFIG_DIR_VAR . DIRECTORY_SEPARATOR . self::$currentMode;
	}

	/**
	 * get the temp directory
	 *
	 * @return string
	 */
	public static function getTempDir()
	{
		return self::getVarDir() . DIRECTORY_SEPARATOR . 'tmp';
	}

	/**
	 * get a directory in the dynamic htdocs
	 * 
	 * @param string $module
	 * 
	 * @return string 
	 */
	public static function getHtdocsVarDir($module)
	{
		return self::getVarDir() . DIRECTORY_SEPARATOR . 'htdocs' . DIRECTORY_SEPARATOR . 'modulesVar' . DIRECTORY_SEPARATOR . $module;
	}

	/**
	 * get the current cache directory
	 *
	 * @return string
	 */
	public static function getCacheDir()
	{
		return \Foomo\CORE_CONFIG_DIR_VAR . DIRECTORY_SEPARATOR . self::$currentMode . \DIRECTORY_SEPARATOR . 'cache';
	}

}
