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

/**
 * foomo bootstrap - this is not a config file ;)
 *
 * DO NOT EDIT IT !!!
 *
 */

namespace Foomo;

define('Foomo\\SYSTEM_START_MICRO_TIME', microtime(true));

// @todo refactor this to cookies
if(isset($_SERVER['FOOMO_MAINTENANCE']) && (!isset($_SERVER['FOOMO_USER_AGENT']) || $_SERVER['FOOMO_USER_AGENT'] != 'foomo maintenance')) {
	header('HTTP/1.1 503 Service Unavailable');
	if(file_exists($_SERVER['FOOMO_MAINTENANCE'])) {
		echo file_get_contents($_SERVER['FOOMO_MAINTENANCE']);
	} else {
		echo $_SERVER['FOOMO_MAINTENANCE'];
	}
	exit;
}


define('Foomo\\ROOT', realpath(dirname(__DIR__)));

include_once(ROOT . '/lib/Foomo/Timer.php');

Timer::addMarker('lets go');
Timer::start('foomo bootstrap');

include_once(ROOT . '/lib/Foomo/Modules/Manager.php');


include_once(ROOT . '/lib/Foomo/AutoLoader.php');
include_once(ROOT . '/lib/Foomo/Log/Logger.php');
include_once(ROOT . '/lib/Foomo/Utils.php');
if (!class_exists('Annotation')) include_once(ROOT . '/lib/Foomo/Reflection/addendum-0.4.0/annotations.php');
include_once(ROOT . '/lib/Foomo/Config/AbstractConfig.php');
include_once(ROOT . '/lib/Foomo/Core/DomainConfig.php');
include_once(ROOT . '/lib/Foomo/Config.php');

Utils::addIncludePaths(array(ROOT . DIRECTORY_SEPARATOR . 'lib'));

spl_autoload_register(array('Foomo\\AutoLoader', 'loadClass'));

Timer::addMarker('autoloaders are set up');

//var_dump(class_exists('Foomo\\Log\\Printer'));

/**
 * Includes for the SharedCache and avalanche facade
 */


Timer::addMarker('basic classes are loaded');

// ok then let us go by convention for everything that was not set
$foomoDir = dirname(dirname(\Foomo\ROOT));

define('Foomo\\CORE_CONFIG_DIR_MODULES', $foomoDir . DIRECTORY_SEPARATOR . 'modules');

// var
if (isset($_SERVER['FOOMO_CORE_CONFIG_DIR_VAR'])) {
	define('Foomo\\CORE_CONFIG_DIR_VAR', $_SERVER['FOOMO_CORE_CONFIG_DIR_VAR']);
} else {
	define('Foomo\\CORE_CONFIG_DIR_VAR', $foomoDir . DIRECTORY_SEPARATOR . 'var');
}
// config
if (isset($_SERVER['FOOMO\\CORE_CONFIG_DIR_CONFIG'])) {
	define('Foomo\\CORE_CONFIG_DIR_CONFIG', $_SERVER['FOOMO\\CORE_CONFIG_DIR_CONFIG']);
} else {
	define('Foomo\\CORE_CONFIG_DIR_CONFIG', $foomoDir . DIRECTORY_SEPARATOR . 'config');
}

unset($foomoDir);


try {
	// cache will need the runmode
	Config::init();
	//bootstrap the Foomo\Cache\Manager
	Cache\Manager::bootstrap();

	// start the configuration
	Timer::addMarker('cache is set up');
	if (
			in_array(basename($_SERVER['SCRIPT_FILENAME']), array('hiccup.php', 'setup.php')) &&
			(
				realpath($_SERVER['SCRIPT_FILENAME']) == ROOT . DIRECTORY_SEPARATOR . 'htdocs' . DIRECTORY_SEPARATOR . 'hiccup.php' ||
				realpath($_SERVER['SCRIPT_FILENAME']) == ROOT . DIRECTORY_SEPARATOR . 'htdocs' . DIRECTORY_SEPARATOR . 'setup.php'
			)
	) {
		define('Foomo\\ROOT_HTTP', dirname($_SERVER['PHP_SELF']));

		Setup::checkCoreConfigResources();
		Setup::generateShell();
		trigger_error('entering setup / hickup');
	} else {
		Timer::addMarker('config init is done');
		AutoLoader::getClassMap();
		class_exists('Foomo\Log\Printer');
		Cache\DependencyModel::getInstance()->getDirectory();
		Timer::addMarker('class map is initialized');
		define('Foomo\\ROOT_HTTP', Config::getConf(Module::NAME, Core\DomainConfig::NAME)->rootHttp);
		Timer::addMarker('will initialize modules');
		Modules\Manager::initializeModules();
		Timer::addMarker('modules are initialized');
		Log\Logger::bootstrap();
		Session::init();
		Timer::addMarker('session init is through');
	}
} catch(Exception $e) {
	$setupScript = realpath(\Foomo\CORE_CONFIG_DIR_MODULES . DIRECTORY_SEPARATOR . \Module::NAME . DIRECTORY_SEPARATOR . 'htdocs' . DIRECTORY_SEPARATOR . 'setup.php');
	if(realpath($_SERVER['SCRIPT_FILENAME']) != $setupScript) {

		$setupLink = '/r/setup.php';
		$hiccupLink = '/r/hiccup.php';

		$doc = HTMLDocument::getInstance();
		$doc->addBody('<h1>Error</h1>');
		$doc->addBody(
			'<p>The system can not boot properly: <i>' . $e->getMessage() . '</i><p>' .
			'<p>There are some things you might want to try</p>' .
			'<ul>' .
				'<li>Setup the system: <a href="' . $setupLink . '">' . $setupLink . '</a></li>' .
				((basename($_SERVER['SCRIPT_FILENAME']) != 'hiccup.php')?'<li>hiccup (for example, when you added a class, that did not compile) : <a href="' . $hiccupLink . '">' . $hiccupLink . '</a></li>':'') .
			'</ul>'
		);
		echo $doc;
		exit;
	} else {
		Setup::checkCoreConfigResources();
		Setup::generateShell();
	}
}
Timer::stop('foomo bootstrap');
Timer::addMarker('done in ' . (basename(__FILE__)));
