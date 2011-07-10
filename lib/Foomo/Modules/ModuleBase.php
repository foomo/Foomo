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

/**
 * base class if you want to build your own module
 * and by the way there is a wizard in the backend to create modules
 *
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
abstract class ModuleBase
{
	//---------------------------------------------------------------------------------------------
	// ~ Constants
	//---------------------------------------------------------------------------------------------

	const VERSION = '0.1.1';

	//---------------------------------------------------------------------------------------------
	// ~ Public static methods
	//---------------------------------------------------------------------------------------------

	/**
	 * include paths - called before the module is initialized
	 *
	 * @return string[]
	 */
	public static function getIncludePaths()
	{
		return array();
	}

	/**
	 * initialize you module here may add some auto loading, will also be called, when switching between modes with Foomo\Config::setMode($newMode)
	 */
	public static function initializeModule()
	{
	}

	/**
	 * describe your module - text only
	 *
	 * @return string
	 */
	public static function getDescription()
	{
		return get_called_class() . ' is a foomo module without a description';
	}

	/**
	 * get a view for an app
	 *
	 * @param mixed $app instance or class name
	 * @param string $template relative path from /path/to/your/module/teplates
	 * @param mixed $model whatever your model may be
	 *
	 * @return Foomo\View
	 */
	public static function getView($app, $template, $model = null)
	{
		if (!file_exists($template)) {
			if(substr($template, -4) != '.tpl') $template .= '.tpl';
			if (is_object($app)) {
				$className = get_class($app);
			} else {
				$className = $app;
			}
			if (strpos($className, '\\') !== false) {
				// we have a namespace - let us prepend it
				$classNameArray = explode('\\', $className);
				$template = implode(DIRECTORY_SEPARATOR, array_slice($classNameArray, 0, count($classNameArray)-1)) . DIRECTORY_SEPARATOR . $template;
			}
			// pick the right directory
			$moduleName = constant(\get_called_class() . '::NAME');
			$template = \Foomo\CORE_CONFIG_DIR_MODULES . DIRECTORY_SEPARATOR . $moduleName . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . $template;
		}
		return \Foomo\View::fromFile($template, $model);
	}

	/**
	 * get a module translation for an app
	 *
	 * @param mixed $app instance or class name
	 * @param type $localeChain
	 *
	 * @return Foomo\Translation
	 */
	public static function getTranslation($app, $localeChain = null)
	{
		// locale/Foomo/My/App/en.yml
		$calledClassName = get_called_class();
		if(is_object($app)) {
			$namespace = get_class($app);
		} else {
			$namespace = $app;
		}
		return \Foomo\Translation::getModuleTranslation(constant($calledClassName . '::NAME'), $namespace, $localeChain);
	}

	/**
	 * get all the module resources
	 *
	 * @return Resource
	 */
	public static function getResources()
	{
		return array();
	}

	/**
	 * @return string
	 */
	public static function getCacheDir($pathname='')
	{
		$ret = \Foomo\Config::getCacheDir(self::getModuleName());
		if ($pathname != '') $ret .= DIRECTORY_SEPARATOR . $pathname;
		if (!file_exists($ret)) Resource\Fs::getAbsoluteResource(Resource\Fs::TYPE_FOLDER, $ret)->tryCreate();
		return $ret;
	}

	/**
	 * @return string
	 */
	public static function getTempDir($pathname='')
	{
		$ret = \Foomo\Config::getTempDir(self::getModuleName());
		if ($pathname != '') $ret .= DIRECTORY_SEPARATOR . $pathname;
		if (!file_exists($ret)) Resource\Fs::getAbsoluteResource(Resource\Fs::TYPE_FOLDER, $ret)->tryCreate();
		return $ret;
	}

	/**
	 * @param string $pathname append optional additional relative path
	 * @return string
	 */
	public static function getVarDir($pathname='')
	{
		$ret = \Foomo\Config::getVarDir(self::getModuleName());
		if ($pathname != '') $ret .= DIRECTORY_SEPARATOR . $pathname;
		if (!file_exists($ret)) Resource\Fs::getAbsoluteResource(Resource\Fs::TYPE_FOLDER, $ret)->tryCreate();
		return $ret;
	}

	/**
	 * @param string $pathname append optional additional relative path
	 * @return string
	 */
	public static function getHtdocsVarDir($pathname='')
	{
		$ret = \Foomo\Config::getHtdocsVarDir(self::getModuleName());
		if ($pathname != '') $ret .= DIRECTORY_SEPARATOR . $pathname;
		if (!file_exists($ret)) Resource\Fs::getAbsoluteResource(Resource\Fs::TYPE_FOLDER, $ret)->tryCreate();
		return $ret;
	}

	/**
	 * @param string $pathname append optional additional relative path
	 * @return string
	 */
	public static function getHtdocsVarPath($pathname='')
	{
		$ret = \Foomo\Config::getHtdocsVarPath(self::getModuleName());
		if ($pathname != '') $ret .= DIRECTORY_SEPARATOR . $pathname;
		return $ret;
	}

	/**
	 * @return string
	 */
	public static function getLogDir()
	{
		return \Foomo\Config::getLogDir(self::getModuleName());
	}

	/**
	 * @param string $pathname append optional additional relative path
	 * @return string
	 */
	public static function getBaseDir($pathname='')
	{
		$ret = \Foomo\Config::getModuleDir(self::getModuleName());
		if ($pathname != '') $ret .= DIRECTORY_SEPARATOR . $pathname;
		if (!file_exists($ret)) throw new \Exception('Path ' . $ret . ' does not exist! ');
		return $ret;
	}

	/**
	 * @param string $pathname append optional additional relative path
	 * @return string
	 */
	public static function getTestsDir($pathname='')
	{
		$ret = self::getBaseDir('tests');
		if ($pathname != '') $ret .= DIRECTORY_SEPARATOR . $pathname;
		return $ret;
	}

	/**
	 * @param string $pathname append optional additional relative path
	 * @return string
	 */
	public static function getDocsDir($pathname='')
	{
		$ret = self::getBaseDir('docs');
		if ($pathname != '') $ret .= DIRECTORY_SEPARATOR . $pathname;
		return $ret;
	}

	/**
	 * @param string $pathname append optional additional relative path
	 * @return string
	 */
	public static function getLibDir($pathname='')
	{
		$ret = self::getBaseDir('lib');
		if ($pathname != '') $ret .= DIRECTORY_SEPARATOR . $pathname;
		return $ret;
	}

	/**
	 * @param string $pathname append optional additional relative path
	 * @return string
	 */
	public static function getViewsDir($pathname='')
	{
		$ret = self::getBaseDir('views');
		if ($pathname != '') $ret .= DIRECTORY_SEPARATOR . $pathname;
		return $ret;
	}

	/**
	 * @param string $pathname append optional additional relative path
	 * @return string
	 */
	public static function getVendorDir($pathname='')
	{
		$ret = self::getBaseDir('vendor');
		if ($pathname != '') $ret .= DIRECTORY_SEPARATOR . $pathname;
		return $ret;
	}

	/**
	 * @param string $pathname append optional additional relative path
	 * @return string
	 */
	public static function getHtdocsDir($pathname='')
	{
		$ret = \Foomo\Config::getHtdocsDir(self::getModuleName());
		if ($pathname != '') $ret .= DIRECTORY_SEPARATOR . $pathname;
		return $ret;
	}

	/**
	 * @param string $pathname append optional additional relative path
	 * @return string
	 */
	public static function getHtdocsPath($pathname='')
	{
		$ret = \Foomo\Config::getHtdocsPath(self::getModuleName());
		if ($pathname != '') $ret .= DIRECTORY_SEPARATOR . $pathname;
		return $ret;
	}

	/**
	 * @param string $name
	 * @param string $domain
	 */
	public static function getConfig($name, $domain='')
	{
		return \Foomo\Config::getConf(self::getModuleName(), $name, $domain);
	}

	/**
	 * @param Foomo\Config\AbstractConfig $conf
	 * @param string $domain
	 * @return boolean
	 */
	public static function setConfig(\Foomo\Config\AbstractConfig $conf, $domain='')
	{
		return \Foomo\Config::setConf($conf, self::getModuleName(), $domain);
	}

	/**
	 * does a config for a module exist or not
	 *
	 * @param string $name name of the config
	 * @param string $domain you need multiple for a domain in a module - here you are
	 * @return boolean
	 */
	public static function confExists($name, $domain='')
	{
		return \Foomo\Config::confExists(self::getModuleName(), $name, $domain);
	}

	//---------------------------------------------------------------------------------------------
	// ~ Protected static methods
	//---------------------------------------------------------------------------------------------

	/**
	 * @return string defined module name
	 */
	protected static function getModuleName()
	{
		return (!$name = constant(get_called_class() . '::NAME')) ? str_replace('\\', '.', get_called_class()) : $name;
	}
}