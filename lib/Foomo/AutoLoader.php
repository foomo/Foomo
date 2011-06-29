<?php

/*
 * bestbytes-copyright-placeholder
 */

namespace Foomo;

use Foomo\Modules\Manager;
use ReflectionClass;
use Exception;

/**
 * class for handling class __autoloading in arbitrary projects
 */
class AutoLoader
{
	//---------------------------------------------------------------------------------------------
	// ~ Constants
	//---------------------------------------------------------------------------------------------

	const CACHE_PATH = 'core';

	//---------------------------------------------------------------------------------------------
	// ~ Static variables
	//---------------------------------------------------------------------------------------------

	/**
	 * @var array
	 */
	private static $classMap;

	//---------------------------------------------------------------------------------------------
	// ~ Variables
	//---------------------------------------------------------------------------------------------

	/**
	 * @var array
	 */
	private $alreadyScanned = array();
	/**
	 * @var string
	 */
	private $cacheId;
	/**
	 * array of valid file endings for files that could contain classes
	 *
	 * @var array
	 */
	private $validFileEndings = array('.php');

	//---------------------------------------------------------------------------------------------
	// ~ Constructor
	//---------------------------------------------------------------------------------------------

	/**
	 * the constructor initializes cachePath and cacheId
	 *
	 */
	private function __construct()
	{
		$this->cacheId = 'Foomo_AutoLoader' . md5(implode(',', Manager::getLibFolders()));
	}

	//---------------------------------------------------------------------------------------------
	// ~ Public static methods
	//---------------------------------------------------------------------------------------------

	/**
	 * @return boolean
	 */
	public static function getClassMapAvailable()
	{
		return isset(self::$classMap);
	}

	/**
	 * @param string $interface
	 * @return string[]
	 */
	public static function getClassesByInterface($interface)
	{
		return \Foomo\Cache\Proxy::call(__CLASS__, 'cachedGetClassesByInterface', array((string) $interface));
	}

	/**
	 * singleton
	 *
	 * @internal
	 * @return Foomo\AutoLoader
	 */
	public static function getInstance()
	{
		static $inst;
		if (!isset(self::$inst)) {
			$inst = new self();
		}
		return $inst;
	}

	/**
	 * get all the classes the auto oader knows of
	 *
	 * @return array a hash of class name => file name the class was defined in
	 */
	public static function getClassMap()
	{
		if (self::$classMap === null) {
			$resource = \Foomo\Cache\EmptyResourceHack::getEmptyResource('Foomo\\AutoLoader', 'cachedGetClassMap', array(), array(), 0);
			$cachedResource = \Foomo\Cache\Manager::load($resource);
			if ($cachedResource == null) {
				self::$classMap = self::getInstance()->buildClassMap();
				$resource->value = self::$classMap;
				\Foomo\Cache\Manager::save($resource);
			} else {
				self::$classMap = $cachedResource->value;
			}
		}
		return self::$classMap;
	}

	/**
	 * get the file name for a class
	 *
	 * @param string $className name of the class
	 * @return string file name
	 */
	public static function getClassFileName($className)
	{
		$lowerClassName = $className;
		$classMap = self::getClassMap();
		if (isset($classMap[$lowerClassName])) {
			return $classMap[$lowerClassName];
		}
	}

	/**
	 * get the classes defined in a file
	 *
	 * @param string $fileName name of the file
	 * @return string[]
	 */
	public static function getClassesByFileName($fileName)
	{
		$fileName = realpath($fileName);
		$ret = array();
		foreach (self::getClassMap() as $className => $classFileName) {
			if ($fileName == $classFileName) {
				$ret[] = $className;
			}
		}
		return $ret;
	}

	/**
	 * @param string $className
	 * @return boolean
	 */
	public static function pathAutoload($className)
	{
		//var_dump(__METHOD__ . ' ' . $className);
		if (strpos($className, '\\') !== false) {
			// ns
			$needle = '\\';
		} else if (strpos($className, '_') !== false) {
			// pear
			$needle = '_';
		} else {
			// naked class
			$needle = '';
		}
		$classFile = str_replace($needle, DIRECTORY_SEPARATOR, $className) . '.php';
		return self::tryInclude($classFile, $className);
	}

	/**
	 * this is where __autoload lands
	 * but you can call it yourself too for example from your __autoload
	 *
	 * @param string $className the name of the class you need
	 * @return boolean
	 */
	public static function loadClass($className)
	{
		// let us begin
		// the classmap is being loaded externally from foomo.inc.php
		if(isset(self::$classMap)) {
			// if self::$classmap is loaded, we try to use it and fall back to
			// conventional auto loading, if we fail there
			if (empty($className)) {
				throw new \InvalidArgumentException('empty classNames are not valid', 1);
			}
			if (!isset(self::$classMap)) {
				$tmp = new self();
				self::$classMap = self::getClassMap();
			}
			if (isset(self::$classMap[$className])) {
				try {
					if (false !== $ret = include_once(self::$classMap[$className])) {
						return true;
					} else {
						trigger_error('could not include ' . self::$classMap[$className] . ' for declaration of ' . $className . '.', E_USER_WARNING);
						return false;
					}
				} catch (Exception $e) {
					trigger_error('could not load a class file for ' . $className . ' => ' . $e->getMessage(), E_USER_WARNING);
					return false;
				}
			} else {
				// fallback
				return self::pathAutoload($className);
			}
		} else {
			// if self::$classMap is not loaded, we will try class loading by
			// convention from the include_path
			return self::pathAutoload($className);
		}
	}

	/**
	 * recursively load all classes in a directory
	 *
	 * @experimental
	 * @param string $directory
	 */
	public static function loadClassesInDir($directory)
	{
		foreach (self::getClassMap() as $name => $file) {
			if (strpos(realpath($file), $directory) === 0) {
				self::loadClass($name);
			}
		}
	}

	/**
	 * create the class map a hash of 'classname' => '/path/to/class/file.class.php'
	 *
	 * @return array
	 */
	public function buildClassMap($silently=false)
	{
		if (!$silently) {
			trigger_error(PHP_EOL . __METHOD__ . 'building a new classmap from :' . PHP_EOL . '  ' . implode(PHP_EOL . '  ', Manager::getLibFolders()) . PHP_EOL, E_USER_NOTICE);
		}
		$fileArray = array();

		foreach (Manager::getLibFolders() as $rootFolder) {
			$rootFolder = trim($rootFolder);
			if (strpos($rootFolder, '.') === 0) {
				trigger_error('discarding a relative path ' . $rootFolder, E_USER_NOTICE);
			} else {
				$this->lsR($rootFolder, $fileArray);
			}
		}
		$classMap = array();
		foreach ($fileArray as $fileName) {
			foreach ($this->validFileEndings as $validFileEnding) {
				if (substr($fileName, strlen($fileName) - strLen($validFileEnding)) == $validFileEnding) {
					$classNames = $this->scanForClasses($fileName);
					foreach ($classNames as $className) {
						if (array_key_exists($className, $classMap) && $classMap[$className] != $fileName) {
							//if(!$silently) {
							trigger_error($className . ' was already found in ' . $classMap[$className] . ' ignoring ' . $fileName, E_USER_WARNING);
							//}
						} else {
							$classMap[$className] = $fileName;
						}
					}
					break;
				}
			}
		}
		if (!$silently) {
			trigger_error(__METHOD__ . ' putting classmap to cache with ' . count($classMap) . ' classes', E_USER_NOTICE);
		}

		return $classMap;
	}

	/**
	 * Place reset functions here
	 *
	 * @internal
	 *
	 * @return string HTML
	 */
	public static function resetCache()
	{
		\Foomo\Hiccup::removeAutoloaderCache();
		self::reset(true);
		$ret = '<b>loaded classes from ' . implode(', ', Manager::getLibFolders()) . '</b><pre>';
		$map = self::getClassMap();
		$keys = array_keys($map);
		sort($keys, SORT_STRING);
		foreach ($keys as $key) {
			try {
				$ref = new ReflectionClass($key);
				$ret .= $ref->name . ' in ' . $map[$key] . PHP_EOL;
			} catch (Exception $e) {
				$ret .= '<span style="color:#ff0000">broken reference : ' . $key . ' can not be loaded from ' . $map[$key] . '</span>' . PHP_EOL;
			}
		}
		return $ret . '</pre>';
	}

	//---------------------------------------------------------------------------------------------
	// ~ Cached methods
	//---------------------------------------------------------------------------------------------

	/**
	 * @Foomo\Cache\CacheResourceDescription()
	 */
	public static function cachedGetClassMap()
	{//do nothing. not using proxy inhere
	}

	/**
	 * @Foomo\Cache\CacheResourceDescription()
	 * @param string $interface
	 * @return string[]
	 */
	public static function cachedGetClassesByInterface($interface)
	{
		$ret = array();
		foreach (self::getClassMap() as $className => $classFileName) {
			$refl = new ReflectionClass($className);
			if ($refl->implementsInterface($interface) && !$refl->isAbstract()) $ret[] = $refl->getName();
		}
		return $ret;
	}

	//---------------------------------------------------------------------------------------------
	// ~ Private methods
	//---------------------------------------------------------------------------------------------

	/**
	 * @param string $classFile
	 * @param string $className
	 * @return boolean
	 */
	private static function tryInclude($classFile, $className)
	{
		if(self::includeExists($classFile)) {
			try {
				include_once($classFile);
				if (class_exists($className, false) || interface_exists($className, false)) {
					return true;
				} else {
					return false;
				}
			} catch (Execption $e) {
				trigger_error('autoloading ' . $className . ' from ' . $classFile . ' caused a problem ' . $e->getMessage(), E_USER_WARNING);
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * @return string
	 */
	private static function includeExists($includeFile)
	{
		$fp = @fopen($includeFile, 'r', true);
		$ret = is_resource($fp);
		if ($ret) fclose($fp);
		return $ret;
	}

	/**
	 * @param boolean $silently
	 * @return string[]
	 */
	private static function reset($silently=false)
	{
		$tmp = new self();
		\Foomo\Cache\Manager::reset(__CLASS__.'::cachedGetClassesByInterface', false);
		return self::$classMap = $tmp->buildClassMap($silently);
	}

	/**
	 * scan for classes in a file with a given filename
	 * It is simple parser, that is designed to be transparent and relies on code which is NOT encoded, but well coded
	 * keeping up with common coding standards
	 *
	 * @todo keep an eye on this little parser
	 * @param string $fileName name of the file
	 * @return array part of the $classMap array('classname' => '/path/to/file/containing/the/class.php')
	 */
	private function scanForClasses($fileName)
	{
		$classNames = array();
		$fileContents = file_get_contents($fileName);
		$tokens = token_get_all($fileContents);
		if (!defined('T_NAMESPACE')) {
			// if we do not know namespaces yet
			$lastError = error_get_last();
			if ($lastError['message'] == "Unexpected character in input:  '\' (ASCII=92) state=1") {
				trigger_error('skipping a file that seems to use namespace syntax ' . $fileName);
				return $classNames;
			}
		}
		$namespace = '';
		for ($i = 0, $max = sizeof($tokens); $i < $max; $i++) {
			// there is a namspace token in this class
			// @todo this code fails on:
			//   namespace Foo {
			// only works on
			//  namespace Foo;
			// => let fix it
			if ($tokens[$i][0] == T_NAMESPACE) {
				$iNamespace = $i + 2;
				$namespace = '';
				while (isset($tokens[$iNamespace]) && is_array($tokens[$iNamespace]) && !in_array($tokens[$iNamespace][1], array('{', ' '))) {
					$namespace .= $tokens[$iNamespace][1];
					$iNamespace++;
				}
			}
			if ($tokens[$i][0] === T_CLASS || $tokens[$i][0] === T_INTERFACE) {
				if (!empty($namespace)) {
					$classNames[] = $namespace . '\\' . $tokens[$i + 2][1];
				} else {
					$classNames[] = $tokens[$i + 2][1];
				}
			}
		}
		return $classNames;
	}

	/**
	 * recursive listing of a directory, will trigger E_USER_NOTICE if a folder is not readable
	 *
	 * @param string $dir the directory to be read recursively
	 * @param array $fileArray pass this array to get the results collected array('/path/to/file1', '/path/to/file2', ...)
	 */
	private function lsR($dir, &$fileArray)
	{
		if (!in_array($dir, $this->alreadyScanned)) {
			array_push($this->alreadyScanned, $dir);
			$dirX = array('.', '..');
			if (is_dir($dir)) {
				if (is_readable($dir)) {
					$dh = opendir($dir);
					if ($dh) {
						while (($file = readdir($dh)) !== false) {
							$fullName = $dir . DIRECTORY_SEPARATOR . $file;
							if (is_dir($fullName) AND !in_array($file, $dirX)) {
								$this->lsR($fullName, $fileArray);
							} else {
								if (!in_array($file, $dirX)) {
									array_push($fileArray, $fullName);
								}
							}
						}
						closedir($dh);
					}
				} else {
					trigger_error('could not scan directory ' . $dir, E_USER_NOTICE);
				}
			}
		}
	}
}
