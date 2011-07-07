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

namespace Foomo\Cache\MockObjects;

/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
class SampleResources {

	/**
	 * @Foomo\Cache\CacheResourceDescription('lifeTime'=10,'dependencies'='Foomo\Cache\MockObjects\SampleResources->getHoroscopeData')
	 *
	 * @param integer $timestamp
	 * @param string $location
	 * @param FancyClass $template
	 * @param double $dbl
	 *
	 * @return string

	 */
	public function noticeMEEEEEEE($timestamp, $location, $template, $dbl) {
		$data = \Foomo\Cache\Proxy::call($this, 'getHoroscopeData', array($timestamp, $location));
		// render on template
		return 'rending on ' . \var_export($data, true) . ' in template ' . $template;
	}

	/**
	 * @Foomo\Cache\CacheResourceDescription()
	 *
	 * @param mixed $param
	 * @return string

	 */
	public function iamAmAmixedMethod($param) {
		return 'A mixed param ' . $param;
	}





	/**
	 * a very useless, yet transparent method
	 *
	 * @Foomo\Cache\CacheResourceDescription
	 *
	 * @param string $foo
	 * @param string $bar
	 *
	 * @return string
	 */
	public static function test($foo, $bar) {
		return 'foo: ' . $foo . ', bar: ' . $bar;
	}

	/**
	 * a very useless, yet transparent method
	 *
	 * @Foomo\Cache\CacheResourceDescription
	 *
	 * @param string $foo
	 * @param string $bar
	 *
	 * @return string
	 */
	public function testNonStatic($foo, $bar) {
		return self::test($foo, $bar);
	}

	/**
	 * @Foomo\Cache\CacheResourceDescription('invalidationPolicy' = 'POLICY_INSTANT_REBUILD')
	 *
	 * @param integer $timestamp
	 * @param string $location
	 *
	 * @return array
	 */
	public function getHoroscopeData($timestamp, $location) {
		return array(
			'horoscopes are bullshit',
			$timestamp,
			$location
		);
	}

	/**
	 * @Foomo\Cache\CacheResourceDescription('dependencies'='Foomo\Cache\MockObjects\SampleResources->getHoroscopeData')
	 *
	 * @param integer $timestamp
	 * @param string $location
	 * @param string $template
	 * @return string
	 */
	public function renderHoroscope($timestamp, $location, $template) {
		$data = \Foomo\Cache\Proxy::call($this, 'getHoroscopeData', array($timestamp, $location));
		// render on template
		return 'rending on ' . \var_export($data, true) . ' in template ' . $template;
	}

	/**
	 * @Foomo\Cache\CacheResourceDescription('dependencies'='Foomo\Cache\MockObjects\SampleResources->getHoroscopeData')
	 *
	 * @param integer $timestamp
	 * @param string $location
	 * @param string $template
	 * @return string
	 */
	public function renderHoroscope3D($timestamp, $location, $template) {
		$data = \Foomo\Cache\Proxy::call($this, 'getHoroscopeData', array($timestamp, $location));
		// render on template
		return 'rending on ' . \var_export($data, true) . ' in template ' . $template;
	}

	/**
	 * @Foomo\Cache\CacheResourceDescription('lifeTimeFast' = 10000, 'dependencies'='Foomo\Cache\MockObjects\SampleResources->renderHoroscope, Foomo\Cache\MockObjects\SampleResources->getAddress')
	 *
	 * @param integer $timestamp
	 * @param string $location
	 * @param string $template
	 * @param string $personName
	 * @return string
	 *
	 *
	 */
	public function sendHosroscopeRendering($timestamp, $location, $template, $personName) {
		$horoscope = \Foomo\Cache\Proxy::call($this, 'renderHoroscope', array($timestamp, $location, $template));
		$address = \Foomo\Cache\Proxy::call($this, 'getAddress', array($personName));
		return 'sending to horoscope : "' . $horoscope . '" to ' . $personName . ' at address : ' . $address;
	}

	/**
	 * @Foomo\Cache\CacheResourceDescription('dependencies'='Foomo\Cache\MockObjects\SampleResources->sendHosroscopeRendering, Foomo\Cache\MockObjects\SampleResources->getAddress')
	 *
	 * @param integer $timestamp
	 * @param string $location
	 * @param string $template
	 * @param string $personName
	 * @return string
	 */
	public function checkHoroscopeReception($timestamp, $location, $template, $personName) {
		$horoscope = \Foomo\Cache\Proxy::call($this, 'renderHoroscope', array($timestamp, $location, $template));
		$address = \Foomo\Cache\Proxy::call($this, 'getAddress', array($personName));
		return 'checked sending horoscope : "' . $horoscope . '" to ' . $personName . ' at address : ' . $address;
	}

	/**
	 * @Foomo\Cache\CacheResourceDescription('dependencies'='Foomo\Cache\MockObjects\SampleResources->sendHosroscopeRendering, Foomo\Cache\MockObjects\SampleResources->getAddress')
	 *
	 * @param integer $timestamp
	 * @param string $location
	 * @param string $template
	 * @param string $personName
	 * @return string
	 */
	public function checkHoroscopeValid($timestamp, $location, $template, $personName) {
		$horoscope = \Foomo\Cache\Proxy::call($this, 'renderHoroscope', array($timestamp, $location, $template));
		$address = \Foomo\Cache\Proxy::call($this, 'getAddress', array($personName));
		return 'checked validity of sent horoscope : "' . $horoscope . '" to ' . $personName . ' at address : ' . $address;
	}

	/**
	 * @Foomo\Cache\CacheResourceDescription
	 *
	 * @param string $personName
	 */
	public function getAddress($personName) {
		return 'address of ' . $personName . ' @ ' . \microtime(true);
	}

}