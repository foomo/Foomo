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

/**
 * translate stuff
 *
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 * @todo implement plural, dual etc.
 * @internal
 */
class Translation
{

	protected $_table = array();
	protected $localeChain;
	protected static $_DEFAULT_LOCALE_CHAIN;

	/**
	 *
	 *
	 * @param string[] $localeRoots an array of folders where to look for localization resources
	 * @param string $namespace name of the resource /root/de_DE/<resourceName>.yml
	 * @param string[] $localeChain your language preferences
	 *
	 */
	public function __construct($localeRoots = null, $namespace = null, $localeChain = null)
	{
		if(!is_null($localeRoots) || !is_null($localeRoots)) {
			if (is_null($localeChain)) {
				$localeChain = self::getDefaultLocaleChain();
			}
			$this->localeChain = $localeChain;
			$this->_table = \Foomo\Cache\Proxy::call(__CLASS__, 'cachedGetLocaleTable', array($localeRoots, $localeChain, $namespace));
		}
	}

	private static function getDefaultLocaleChain()
	{
		if (!isset(self::$_DEFAULT_LOCALE_CHAIN)) {
			// do not pull the default chain into getDefaultChainFromEnv(), because that will break testability
			self::$_DEFAULT_LOCALE_CHAIN = self::getDefaultChainFromEnv();
		}
		return self::$_DEFAULT_LOCALE_CHAIN;
	}



	/**
	 * use this, if you have translations, that inherit from one another
	 *
	 * @param array $namespaceRoots
	 * @param string[] $localeChain
	 *
	 * @return static
	 */
	public static function getExtendedTranslation(array $namespaceRoots, $localeChain = null)
	{
		if(is_null($localeChain)) {
			$localeChain = self::getDefaultLocaleChain();
		}
		return \Foomo\Cache\Proxy::call(__CLASS__, 'cachedGetExtendedTranslation', array($namespaceRoots, $localeChain));
	}

	/**
	 * @internal
	 *
	 * @Foomo\Cache\CacheResourceDescription
	 *
	 *
	 * @param array $namespaceRoots
	 * @param array $localeChain
	 *
	 * @return static
	 */
	public static function cachedGetExtendedTranslation($namespaceRoots, $localeChain)
	{
		$translation = new static();
		$translation->localeChain = $localeChain;
		foreach($namespaceRoots as $namespace => $localeRoots) {
			$translation->_table = array_merge($translation->_table, \Foomo\Cache\Proxy::call(__CLASS__, 'cachedGetLocaleTable', array($localeRoots, $translation->localeChain, $namespace)));
		}
		return $translation;
	}

	public static function setDefaultLocaleChain($localeChain)
	{
		self::$_DEFAULT_LOCALE_CHAIN = $localeChain;
	}

	/**
	 * get the default localeChain from the environment
	 *
	 * @param string[] $fallbacks
	 *
	 * @todo implement locale detection for the command line
	 *
	 * @return string[] array of locales
	 */
	public static function getDefaultChainFromEnv($fallbacks = ['en', 'de'])
	{
		if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
			// fetch locales and quality from accept language header (http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.4)
			$locales = [];
			foreach(explode(',', $_SERVER["HTTP_ACCEPT_LANGUAGE"]) as $acceptLanguage) {
				$pos = strpos($acceptLanguage, ';q=');
				if($pos === false) {
					$locale = $acceptLanguage;
					$quality = 1.0;
				} else {
					$locale = substr($acceptLanguage, 0, $pos);
					$quality = substr($acceptLanguage, $pos+3);
				}

				// format locale as any two-letter primary-tag is an ISO-639 language abbreviation and any two-letter initial subtag is an ISO-3166 country code
				// http://www.w3.org/Protocols/rfc2616/rfc2616-sec3.html#sec3.10
				$locale = strtolower($locale);
				$localeParts = explode('-', $locale);
				if(count($localeParts) == 2) {
					if($localeParts[1] == '*') {
						$locale = $localeParts[0];
					} else {
						$locale = $localeParts[0] . '_' . strtoupper($localeParts[1]);
					}
				}

				// filter asterisk
				if($locale == '*') {
					continue;
				}

				$locales[] = [
					'locale' => $locale,
					'quality' => $quality,
				];
			}

			// sort locales by quality
			usort($locales, function($a, $b) {
				return($a['quality'] <= $b['quality']);
			});

			$localeChain = [];
			foreach($locales as $locale) {
				$localeChain[] = $locale['locale'];
			}

			// add fallbacks
			foreach ($fallbacks as $fallback) {
				if (!in_array($fallback, $localeChain)) {
					$localeChain[] = $fallback;
				}
			}

			return $localeChain;
		} else {
			return $fallbacks;
		}
	}

	/**
	 * @param string|array $msgId
	 *
	 * @return bool
	 */
	public function hasMessage($msgId, $count = null)
	{
		if(is_string($msgId)) {
			return isset($this->_table[$msgId]);
		} else if(is_array($msgId) && !empty($msgId) && !is_null($count)) {
			$id = $this->getMessageIdForCount($msgId, $count);
			if(!is_null($id)) {
				return isset($this->_table[$id]);
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	private function getMessageIdForCount(array $msgId, $count = null)
	{
		$msgId = array_reverse($msgId);
		foreach ($msgId as $id => $minCount) {
			if ($count >= $minCount) {
				return $id;
			}
		}
		return null;
	}
	/**
	 * @param string|array $msgId or array msgId => minCount
	 * @param int $count
	 * @return array|int|string
	 */
	public function _($msgId, $count = null)
	{
		if (is_array($msgId)) {
			$id = $this->getMessageIdForCount($msgId, $count);
			return isset($this->_table[$id]) ? $this->_table[$id] : $id;
		} else {
			return isset($this->_table[$msgId]) ? $this->_table[$msgId] : $msgId;
		}
	}
	/**
	 * internal message table
	 * 
	 * @return array array('KEY' => 'value')
	 */
	public function getMessageTable()
	{
		return $this->_table;
	}
	public static function getMessage($localeRoots, $resourceName, $localeChain, $msgId, $msgIdPlural = null, $count = null)
	{
		$locale = new self($localeRoots, $resourceName, $localeChain);
		return $locale->_($msgId, $msgIdPlural, $count);
	}
	/**
	 *
	 *
	 * @internal
	 *
	 * @Foomo\Cache\CacheResourceDescription
	 *
	 * @param array $localeRoots
	 * @param array $localeChain
	 * @param string $namespace
	 *
	 * @return array
	 */
	public static function cachedGetLocaleTable($localeRoots, $localeChain, $namespace)
	{
		$ret = [];
		$localeRoots = array_reverse($localeRoots);
		$localeChain = array_reverse($localeChain);
		foreach ($localeRoots as $localeRoot) {
			foreach ($localeChain as $locale) {
				// test for a locale file (de_DE.yml)
				$fileName = self::getResourceFileName($localeRoot, $locale, $namespace);
				if (file_exists($fileName)) {
					$fileContents = file_get_contents($fileName);
					if(!empty($fileContents)) {
						try {
							$data = \Foomo\Yaml::parse($fileContents);
							$ret = array_merge($ret, $data);
						} catch(\Exception $e) {
							trigger_error("could not parse yaml: " . $fileName . ' ' . $e->getMessage(), E_USER_WARNING);
						}
					} else {
						trigger_error("empty locale in: " . $fileName, E_USER_WARNING);
					}
				}

				// match language of locale, e.g. de.yaml of de_CH
				if(strlen($locale) > 2 && !in_array(substr($locale, 0, 2), $localeChain)) {
					$fileName = self::getResourceFileName($localeRoot, substr($locale, 0, 2), $namespace);
					if (file_exists($fileName)) {
						$ret = array_merge($ret, \Foomo\Yaml::parse(file_get_contents($fileName)));
					}
				}
			}
		}
		return $ret;
	}
	private static function getResourceFileName($localeRoot, $locale, $namespace)
	{
		$fileName = $localeRoot . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR . $locale. '.yml';
		return $fileName;
	}
	/**
	 * get a translation for a module
	 *
	 * @param string $moduleName
	 * @param string $namespace
	 * @param string $localeChain
	 *
	 * @return \Foomo\Translation
	 */
	public static function getModuleTranslation($moduleName, $namespace, $localeChain = null)
	{
		$rootBase = \Foomo\CORE_CONFIG_DIR_MODULES;
		return new self(
			array($rootBase . DIRECTORY_SEPARATOR . $moduleName . DIRECTORY_SEPARATOR . 'locale'),
			$namespace,
			$localeChain
		);
	}
}
