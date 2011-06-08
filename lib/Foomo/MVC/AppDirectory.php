<?php

/*
 * bestbytes-copyright-placeholder
 */

namespace Foomo\MVC;

use Foomo\AutoLoader;
use ReflectionClass;
use Foomo\Cache\Proxy;

/**
 * helps to resolve apps when only the app id is known
 */
class AppDirectory {

	/**
	 * resolve an app by its id
	 *
	 * @param string $appId app id
	 * 
	 * @return string name of the corresponding app class name
	 */
	public static function resolveClass($appId)
	{
		$directory = self::getAppClassDirectory();
		if (isset($directory[$appId])) {
			return $directory[$appId];
		} else {
			return false;
		}
	}

	/**
	 * @Foomo\Cache\CacheResourceDescription('dependencies'='Foomo\AutoLoader::cachedGetClassMap')
	 *
	 * @return array a hash with id => class name mapping
	 *
	 */
	public static function cachedGetAppClassDirectory()
	{
		$classMap = AutoLoader::getClassMap();
		$ret = array();
		foreach ($classMap as $className => $classFileName) {
			try {
				$refl = new ReflectionClass($className);
				if (!$refl->isAbstract() && $refl->isSubclassOf('Foomo\\MVC\\AbstractApp')) {
					$appId = $refl->getConstant('NAME');
					if (!$appId) {
						$appId = str_replace('\\', '.', $refl->getName());
					}
					$ret[$appId] = $refl->getName();
				}
			} catch (Exception $e) {
				
			}
		}
		return $ret;
	}

	public static function getAppClassDirectory()
	{
		return Proxy::call(__CLASS__, 'cachedGetAppClassDirectory');
	}

}