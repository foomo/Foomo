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

use Foomo\Log\Logger;
use Foomo\Modules\Manager;
use Foomo\MVC\AbstractApp;
use Foomo\MVC\URLHandler;
use Foomo\MVC\View as MVCView;
use Foomo\Timer;
use ReflectionClass;

/**
 * a simple MVC implementation
 *
 * @link    www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author  jan <jan@bestbytes.de>
 */
class MVC
{
	// --------------------------------------------------------------------------------------------
	// ~ Static variables
	// --------------------------------------------------------------------------------------------

	/**
	 * @var array
	 */
	private static $pathArray = array();
	/**
	 * views / partial, that were caught
	 *
	 * @var array
	 */
	private static $caughtViews = array();
	/**
	 * views / their path which are to be caught. if empty array, all will be
	 * caught, if entries, the entries will be caught
	 *
	 * @var mixed
	 */
	private static $catchViews = false;
	/**
	 * catch mode currently on or of
	 *
	 * @internal
	 * @var boolean
	 */
	public static $catchingViews = false;
	/**
	 * hide whatever php from the path
	 *
	 * @var bool
	 */
	protected static $hideScript = false;

	// --------------------------------------------------------------------------------------------
	// ~ Variables
	// --------------------------------------------------------------------------------------------

	/**
	 * @var int
	 */
	private static $level = 0;
	/**
	 * @var bool
	 */
	private static $aborted = false;
	/**
	 * @var URLHandler[]
	 *
	 * @internal
	 */
	public static $handlers = array();

	// --------------------------------------------------------------------------------------------
	// ~ Public static methods
	// --------------------------------------------------------------------------------------------

	/**
	 * @param bool $hide
	 */
	public static function hideScript($hide)
	{
		self::$hideScript = (bool) $hide;
	}

	/**
	 * run an MVC application
	 *
	 * @param mixed   $app                 name or application instance
	 * @param string  $baseURL             inject a baseURL
	 * @param boolean $forceBaseURL        force injection of a baseURL
	 * @param boolean $forceNoHTMLDocument force no html document rendering
	 * @param string  $urlHandlerClass
	 * @return string
	 */
	public static function run(
		$app,
		$baseURL = null,
		$forceBaseURL = false,
		$forceNoHTMLDocument = false,
		$urlHandlerClass = 'Foomo\\MVC\\URLHandler'
	)
	{
		//Timer::start(__METHOD__);
		self::$aborted = false;
		// set up the application

		if (is_string($app)) {
			$app = new $app;
		}

		// set up the url handler and pass it the application id (still can be overwritten by the controller id)
		$handler = self::prepare($app, $baseURL, $forceBaseURL, $urlHandlerClass);

		Logger::transactionBegin($transActionName = __METHOD__ . ' ' . $handler->getControllerId());

		$exception = self::execute($app, $handler);

		$ret = null;
		if (!self::$aborted) {
			$ret = self::render($app, $handler, $exception, $forceNoHTMLDocument);
			Logger::transactionComplete($transActionName);
		} else {
			Logger::transactionComplete($transActionName, 'mvc aborted');
		}
		//Timer::stop(__METHOD__);
		return $ret;
	}

	/**
	 * @param mixed  $app
	 * @param string $baseURL
	 * @param bool   $forceBaseURL
	 * @param string $urlHandlerClass
	 * @return mixed
	 */
	public static function prepare(
		$app,
		$baseURL = null,
		$forceBaseURL = false,
		$urlHandlerClass = 'Foomo\\MVC\\URLHandler'
	)
	{
		// set up the url handler and pass it the application id (still can be ovewwritten by the controller id)
		$handler = new $urlHandlerClass($app, self::getBaseUrl($baseURL, $forceBaseURL));
		self::$pathArray[] = $handler->getControllerId();
		// we need those to redirect stuff
		$handlerKey = '/' . implode('/', self::$pathArray);
		self::$handlers[$handlerKey] = $handler;
		return $handler;
	}

	public static function execute($app, URLHandler $handler)
	{
		$exception = null;
		try {
			$handler->control($app);
			if ($app->controller->model != $app->model) {
				$app->model = $app->controller->model;
			}
		} catch (\Exception $exception) {
			// trigger_error($exception->getMessage());
		}
		return $exception;
	}

	/**
	 * @param AbstractApp $app
	 * @param URLHandler  $handler
	 * @param Template    $template
	 * @param \Exception  $exception
	 * @return mixed
	 */
	public static function getView(AbstractApp $app, URLHandler $handler, Template $template, $exception)
	{
		$appRefl = new \ReflectionClass($app);
		$classes = array();
		while (true) {
			$classes[] = $appRefl->getName() . '\\View';
			$appRefl = $appRefl->getParentClass();
			if (!$appRefl || $appRefl->isAbstract()) {
				break;
			}
		}
		$classes[] = __NAMESPACE__ . '\\MVC\\View';
		foreach ($classes as $class) {
			if (class_exists($class)) {
				return new $class($app, $handler, $template, $exception);
			}
		}
	}

	/**
	 * @param AbstractApp $app
	 * @param URLHandler  $handler
	 * @param \Exception  $exception
	 * @param bool        $forceNoHTMLDocument
	 * @return string|HTMLDocument
	 * @throws \Exception
	 */
	public static function render($app, $handler, $exception, $forceNoHTMLDocument = false)
	{
		Timer::start(__METHOD__);
		self::$level++;
		if (!is_null($exception)) {
			$template = self::getExceptionTemplate(get_class($app));
		} else {
			$template = self::getViewTemplate(get_class($app), $handler->lastAction);
		}
		$view = self::getView($app, $handler, $template, $exception);
		$app->view = $view;
		MVCView::$viewStack[] = $view;
		// catching views and partials
		$viewPath = self::getViewCatchingPath();
		$cameInCatching = self::$catchingViews;

		if (is_array(self::$catchViews) && (count(self::$catchViews) == 0 || in_array($viewPath, self::$catchViews))) {
			self::$catchingViews = true;
			if (!isset(self::$caughtViews[$viewPath])) {
				self::$caughtViews[$viewPath] = array('view' => 'empty', 'partials' => array());
			}
		}
		$rendering = $view->render();
		if (self::$catchingViews) {
			self::$caughtViews[$viewPath]['view'] = $rendering;
		}

		if (!$cameInCatching) {
			self::$catchingViews = false;
		}
		array_pop(MVCView::$viewStack);
		array_pop(self::$pathArray);

		$app->view = null;

		if (self::$level == 1 && !$forceNoHTMLDocument) {
			$doc = HTMLDocument::getInstance();
			$doc->addBody($rendering);
			Timer::addMarker(__CLASS__ . ' is done');
			$ret = $doc;
		} else {
			self::$level--;
			$ret = $rendering;
		}
		Timer::stop(__METHOD__);
		return $ret;
	}

	/**
	 * @param AbstractApp $app
	 * @param string      $action
	 * @param array       $parameters
	 * @param null        $baseURL
	 * @param bool        $forceNoHTMLDocument
	 * @return string|HTMLDocument
	 */
	public static function runAction($app, $action, $parameters = array(), $baseURL = null, $forceNoHTMLDocument = true)
	{
		try {
			$action = 'action' . ucfirst($action);
			call_user_func_array(array($app->controller, $action), $parameters);
			$app->model = $app->controller->model;
			$exception = null;
		} catch (\Exception $exception) {
			trigger_error($exception->getMessage());
		}
		$handler = new URLHandler($app, $baseURL);
		$handler->lastAction = $action;
		return self::render($app, $handler, $exception, $forceNoHTMLDocument);
	}

	/**
	 * @param string $name
	 * @param int    $level
	 * @param string $rendering
	 */
	public static function catchPartial($name, $level, $rendering)
	{
		if ($level > 0) {
			$name .= '-' . $level;
		}
		$target = &self::$caughtViews[self::getViewCatchingPath()]['partials'][$name];
		if (!is_array($target)) {
			$target = array();
		}
		$target[] = $rendering;
	}

	/**
	 * tell the framework to catch views and partials
	 *
	 * @param string $viewPaths what views / partials to catch if empty, everything will be caught
	 */
	public static function catchViews($viewPaths = array())
	{
		self::$catchViews = $viewPaths;
	}

	/**
	 * return caught views / partials as ordered with self::catchViews()
	 *
	 * @return array ('view/path' => 'view' => 'view rendering', 'partials' => array('partial-0' => 'partial rendering'), 'view/other/path' => ...)
	 */
	public static function getCaughtViews()
	{
		return self::$caughtViews;
	}

	/**
	 * @todo: added force as there i cant set the baseurl otherwise
	 *
	 * @param string  $baseURL
	 * @param boolean $force
	 * @return string
	 */
	public static function getBaseUrl($baseURL = null, $force = false)
	{
		if ($force && !is_null($baseURL)) {
			return $baseURL;
		} else if (count(MVCView::$viewStack) > 0) {
			return MVCView::$viewStack[count(MVCView::$viewStack) - 1]->path;
		} else if (is_null($baseURL)) {
			if (strpos($_SERVER['REQUEST_URI'], $_SERVER['SCRIPT_NAME']) === 0 || !self::$hideScript) {
				return $_SERVER['SCRIPT_NAME'];
			} else if (strpos($_SERVER['REQUEST_URI'], dirname($_SERVER['SCRIPT_NAME'])) === 0 && self::$hideScript) {
				return dirname($_SERVER['SCRIPT_NAME']);
			} else {
				return '';
			}
		} else if (!is_null($baseURL)) {
			return $baseURL;
		} else {
			return '';
		}
	}

	/**
	 *
	 */
	public static function abort()
	{
		self::$aborted = true;
		while (ob_get_level() > 0) {
			ob_end_clean();
		}
	}

	/**
	 * @param $appClassName
	 * @return string
	 */
	public static function getAppName($appClassName)
	{
		$constName = $appClassName . '::' . 'NAME';
		if (defined($constName)) {
			return constant($constName);
		} else {
			$appName = str_replace('\\', '.', $appClassName);
			if (substr($appName, -9) == '.Frontend') {
				$appName = substr($appName, 0, strlen($appName) - 9);
			}
			return $appName;
		}
	}

	/**
	 * get an asset path for your app
	 * you can inherit them from parent apps too and you will get a warning when
	 * you are referencing assets, that are not there
	 *
	 * @param string $appClassName name of the app class
	 * @param string $assetPath    relative path separated with forward slashes from the htdocs folder in your module
	 *
	 * @return string PATH in the URL
	 */
	public static function getViewAsset($appClassName, $assetPath)
	{
		return self::getViewAssetInPath('modules', $appClassName, $assetPath);
	}

	/**
	 * same like the above
	 *
	 * @see self::getViewAsset
	 *
	 * @param string $appClassName name of the app class
	 * @param string $assetPath    relative path separated with forward slashes from the htdocs folder in your module
	 *
	 * @return string PATH in the URL
	 */
	public static function getViewVarAsset($appClassName, $assetPath)
	{
		return self::getViewAssetInPath('modulesVar', $appClassName, $assetPath);
	}

	/**
	 * get a partial template
	 *
	 * @internal
	 * @return Template
	 */
	public static function getViewPartialTemplate($appClassName, $partialName)
	{
		static $cache = array();
		$id = $appClassName . '-' . $partialName;
		if (!isset($cache[$id])) {
			$templateFileBase = self::getTemplateBase($appClassName);
			$templateFile = $templateFileBase . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . $partialName . '.tpl';
			if (!file_exists($templateFile)) {
				$refl = new ReflectionClass($appClassName);
				$parent = $refl->getParentClass();
				if ($parent && !$parent->isAbstract()) {
					$cache[$id] = self::getViewPartialTemplate($parent->getName(), $partialName);
				} else {
					$cache[$id] = self::getMyTemplate('partialNotFound');
				}
			} else {
				$cache[$id] = new Template($appClassName . '-' . $partialName, $templateFile);
			}
		}
		return $cache[$id];
	}

	/**
	 * @param string $appClassName
	 * @return array
	 */
	public static function getLocaleRoots($appClassName)
	{
		return self::getRoots('locale', $appClassName);
	}

	/**
	 * get the current handler from the stack
	 *
	 * @return \Foomo\MVC\URLHandler
	 */
	public static function getCurrentURLHandler()
	{
		$handlerKey = '/' . implode('/', self::$pathArray);
		$handler = self::$handlers[$handlerKey];
		return $handler;
	}

	/**
	 * @param string $action
	 * @param array  $parameters
	 * @param string $baseURL
	 */
	public static function redirect($action, array $parameters = array(), $baseURL = null)
	{
		self::abort();
		$urlHandler = self::getCurrentURLHandler();
		if (!is_null($baseURL)) {
			$urlHandler->baseURL = $baseURL;
		}
		header('Location: ' . $urlHandler->renderMethodURL($action, $parameters));
		exit;
	}

	/**
	 * for debugging purposes only,
	 * it will be the deepest "path" ,that was reached so far
	 *
	 * @return array
	 * @internal
	 */
	public static function getPathInfo()
	{
		$info = array();
		foreach (self::$handlers as $handler) {
			$info[] = array(
				'action'     => $handler->getControllerId() . '::' . $handler->lastAction,
				'parameters' => $handler->lastParameters
			);
		}
		return $info;
	}

	// --------------------------------------------------------------------------------------------
	// ~ Private static methods
	// --------------------------------------------------------------------------------------------

	/**
	 * @param string $root
	 * @param string $appClassName
	 * @param string $assetPath
	 * @return string
	 */
	private static function getViewAssetInPath($root, $appClassName, $assetPath)
	{
		foreach (self::getAssetRoots($appClassName) as $moduleName => $assetRoot) {
			if (file_exists($assetRoot . DIRECTORY_SEPARATOR . $assetPath)) {
				return \Foomo\ROOT_HTTP . '/' . $root . '/' . $moduleName . '/' . $assetPath;
			}
		}
		trigger_error(
			'asset "' . $assetPath . '" not found for app class "' . $appClassName . '" in root ' . $root,
			E_USER_WARNING
		);
	}

	/**
	 * @return string
	 */
	private static function getViewCatchingPath()
	{
		return implode('/', self::$pathArray);
	}

	/**
	 * @param string $name
	 * @return Template
	 */
	private static function getMyTemplate($name)
	{
		return new Template(
			$name,
			\Foomo\ROOT . DIRECTORY_SEPARATOR .
			'views' . DIRECTORY_SEPARATOR .
			'Foomo' . DIRECTORY_SEPARATOR .
			'MVC' . DIRECTORY_SEPARATOR .
			$name . '.tpl'
		);
	}

	/**
	 * where do the class templates come from
	 *
	 * @param string $appClassName name of the application class
	 *
	 * @return string path to the corresponding folder typically in modules/xyz/views/appName
	 */
	private static function getTemplateBase($appClassName)
	{
		$appClassModule = Manager::getClassModule($appClassName);
		$templateFileBase =
			\Foomo\CORE_CONFIG_DIR_MODULES . DIRECTORY_SEPARATOR .
			$appClassModule . DIRECTORY_SEPARATOR .
			// 'templates' . DIRECTORY_SEPARATOR .
			'views' . DIRECTORY_SEPARATOR .
			//$appId . DIRECTORY_SEPARATOR
			implode(DIRECTORY_SEPARATOR, explode('\\', $appClassName));;
		return $templateFileBase;
	}

	/**
	 * @param string $type
	 * @param string $appClassName
	 * @param bool   $asHash
	 * @return array
	 */
	private static function getRoots($type, $appClassName, $asHash = false)
	{
		$roots = array();
		$refl = new ReflectionClass($appClassName);
		while ($refl) {
			$appClassModule = Manager::getClassModule($appClassName);
			$base = \Foomo\CORE_CONFIG_DIR_MODULES . DIRECTORY_SEPARATOR . $appClassModule;
			if ($asHash) {
				$roots[$appClassModule] = $base . DIRECTORY_SEPARATOR . $type;
			} else {
				$roots[] = $base . DIRECTORY_SEPARATOR . $type;
			}
			$refl = $refl->getParentClass();
		}
		return array_unique($roots);
	}

	/**
	 * @param string $appClassName
	 * @return array
	 */
	private static function getAssetRoots($appClassName)
	{
		return self::getRoots('htdocs', $appClassName, true);
	}

	/**
	 * @param string $appClassName
	 * @return Template
	 */
	private static function getExceptionTemplate($appClassName)
	{
		// exception templates need to find parent stuff too ...
		$appRefl = new ReflectionClass($appClassName);
		while ($appRefl && !$appRefl->isAbstract()) {
			if (file_exists(
				$templateFile = self::getTemplateBase($appRefl->getName()) . DIRECTORY_SEPARATOR . 'exception.tpl')
			) {
				return new Template('Exception-' . $appRefl->getName(), $templateFile);
			}
			$appRefl = $appRefl->getParentClass();
		}
		return new Template('Exception-' . $appClassName, \Foomo\ROOT . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'Foomo' . DIRECTORY_SEPARATOR . 'MVC' . DIRECTORY_SEPARATOR . 'exception.tpl');
	}

	/**
	 * @param string $appClassName
	 * @param string $actionName
	 * @return Template
	 * @throws \Exception
	 */
	private static function getViewTemplate($appClassName, $actionName)
	{
		$templateFileBase = self::getTemplateBase($appClassName);
		$templateFile = $templateFileBase . DIRECTORY_SEPARATOR . strtolower(substr($actionName, 6, 1)) . substr($actionName, 7) . '.tpl';
		if (!file_exists($templateFile)) {
			$templateFile = $defaultTemplateFile = $templateFileBase . DIRECTORY_SEPARATOR . 'default.tpl';
		}
		if (file_exists($templateFile)) {
			$template = new Template($appClassName . '-' . $actionName, $templateFile);
			return $template;
		} else {
			$refl = new ReflectionClass($appClassName);
			$parent = $refl->getParentClass();
			if ($parent && !$parent->isAbstract()) {
				return self::getViewTemplate($parent->getName(), $actionName);
			} else {
				throw new \Exception(
					'Template not found for ' . $appClassName . '/' . $actionName . ' was expected in ' . $templateFile
				);
			}
		}
	}
}
