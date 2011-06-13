<?php

/*
 * bestbytes-copyright-placeholder
 */

namespace Foomo;

use Foomo\Log\Logger;
use Foomo\Timer;
use Foomo\HTMLDocument;
use Foomo\Template;
use Foomo\MVC\URLHandler;
use Foomo\MVC\View as MVCView;
use Foomo\Modules\Manager;
use ReflectionClass;

/**
 * a simple MVC implementation
 * @todo add a router
 * @todo add haml lesscss support
 */
class MVC {

	private static $level = 0;
	public static $handlers = array();
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
	 * run an MVC application
	 *
	 * @param mixed $app name or application instance
	 * @param string $baseURL inject a baseURL
	 * @return string
	 */
	public static function run($app, $baseURL = null)
	{
		// set up the application

		if (is_string($app)) {
			$app = new $app;
		}

		self::$level++;


		// set up the url handler and pass it the application id (still can be ovewwritten by the controller id)


		$handler = new URLHandler($app, self::getBaseUrl($baseURL));

		Logger::transactionBegin($transActionName = __METHOD__ . ' ' . $handler->getControllerId());

		self::$pathArray[] = $handler->getControllerId();

		// we need those to redirect stuff

		$handlerKey = '/' . implode('/', self::$pathArray);

		self::$handlers[$handlerKey] = $handler;


		try {
			$handler->control($app);
			$template = self::getViewTemplate(get_class($app), $handler->lastAction);
			$exception = null;
		} catch (\Exception $exception) {
			trigger_error($exception->getMessage());
			$template = self::getExceptionTemplate(get_class($app));
		}
		$view = new MVCView($app, $handler, $template, $exception);
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

		if (self::$level == 1) {
			$doc = HTMLDocument::getInstance();
			$doc->addBody($rendering);
			Timer::addMarker(__CLASS__ . ' is done');
			$ret = $doc;
		} else {
			self::$level--;
			$ret = $rendering;
		}
		Logger::transactionComplete($transActionName);
		return $ret;
	}

	private static function getViewCatchingPath()
	{
		return implode('/', self::$pathArray);
	}

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
	public static function catchViews($viewPaths= array())
	{
		self::$catchViews = $viewPaths;
	}

	/**
	 * return caught views / partials as ordered with self::catchViews()
	 * 
	 * @return array array('view/path' => 'view' => 'view rendering', 'partials' => array('partial-0' => 'partial rendering'), 'view/other/path' => ... )
	 */
	public static function getCaughtViews()
	{
		return self::$caughtViews;
	}

	public static function getBaseUrl($baseURL = null)
	{
		if (count(MVCView::$viewStack) > 0) {
			return MVCView::$viewStack[count(MVCView::$viewStack) - 1]->path;
		} else if (!$baseURL) {
			$baseURL = $_SERVER['SCRIPT_NAME'];
		}
		return $baseURL;
	}

	public static function abort()
	{
		while (ob_get_level() > 0) {
			ob_end_clean();
		}
	}

	public static function getAppName($appClassName)
	{
		$constName = $appClassName . '::' . 'NAME';
		if (defined($constName)) {
			return constant($constName);
		} else {
			$appName = str_replace('\\', '.', $appClassName);
			if(substr($appName, -9) == '.Frontend') {
				$appName = substr($appName, 0, strlen($appName) - 9);
			}
			return $appName;
		}
	}

	/**
	 * get a partial template
	 * 
	 * @internal
	 * @return Template
	 */
	public static function getViewPartialTemplate($appClassName, $partialName)
	{
		$templateFileBase = self::getTemplateBase($appClassName);
		$templateFile = $templateFileBase . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . $partialName . '.tpl';
		if (!file_exists($templateFile)) {
			$refl = new ReflectionClass($appClassName);
			$parent = $refl->getParentClass();
			if ($parent && !$parent->isAbstract()) {
				return self::getViewPartialTemplate($parent->getName(), $partialName);
			} else {
				return self::getMyTemplate('partialNotFound');
			}
		} else {
			return new Template($appClassName . '-' . $partialName, $templateFile);
		}
	}
	private static function getMyTemplate($name)
	{
		return  new Template(
			$name,
			\Foomo\ROOT . DIRECTORY_SEPARATOR . 
			'views' . DIRECTORY_SEPARATOR . 
			'Foomo' . DIRECTORY_SEPARATOR . 
			'MVC' . DIRECTORY_SEPARATOR .
			$name . '.tpl');		
	}
	/**
	 * where do the class templates come from
	 * @param string $appClassName name of the application class
	 * @return string path to the corresponding folder typically in modules/xyz/views/appName
	 */
	private static function getTemplateBase($appClassName)
	{
		$appClassModule = Manager::getClassModule($appClassName);
		//$appId = self::getAppName($appClassName);
		
		$templateFileBase =
				\Foomo\CORE_CONFIG_DIR_MODULES . DIRECTORY_SEPARATOR .
				$appClassModule . DIRECTORY_SEPARATOR .
				// 'templates' . DIRECTORY_SEPARATOR .
				'views' . DIRECTORY_SEPARATOR .
				//$appId . DIRECTORY_SEPARATOR
				implode(DIRECTORY_SEPARATOR, explode('\\', $appClassName));
		;
		return $templateFileBase;
	}

	public static function getLocaleRoots($appClassName)
	{
		$roots = array();
		$refl = new ReflectionClass($appClassName);
		while ($refl) {
			$appClassModule = Manager::getClassModule($appClassName);
			$appId = self::getAppName($appClassName);
			$localeBase = \Foomo\CORE_CONFIG_DIR_MODULES . DIRECTORY_SEPARATOR . $appClassModule;
			$roots[] = $localeBase . DIRECTORY_SEPARATOR . 'locale';
			$refl = $refl->getParentClass();
		}
		return array_unique($roots);
	}

	private static function getExceptionTemplate($appClassName)
	{
		// exception templates need to find parent stuff too ...
		$appExceptionTempateFile = self::getTemplateBase($appClassName) . DIRECTORY_SEPARATOR . 'exception.tpl';
		if (!file_exists($appExceptionTempateFile)) {
			$appExceptionTempateFile = \Foomo\ROOT . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR .  'Foomo' . DIRECTORY_SEPARATOR .  'MVC' . DIRECTORY_SEPARATOR . 'exception.tpl';
		}
		return new Template('Exception-' . $appClassName, $appExceptionTempateFile);
	}

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
				throw new \Exception('template not found for ' . $appClassName . '/' . $actionName . ' was expected in ' . $templateFile);
			}
		}
	}

	/**
	 * get the current handler from the stack
	 * 
	 * @return Foomo\MVC\URLHandler
	 */
	public static function getCurrentURLHandler()
	{
		$handlerKey = '/' . implode('/', self::$pathArray);
		$handler = self::$handlers[$handlerKey];
		return $handler;
	}

	/**
	 * redirect to another controller / action
	 */
	public static function redirect($action, $parameters = array())
	{
		self::abort();
		header('Location: ' . self::getCurrentURLHandler()->renderMethodURL($action, $parameters));
		exit;
	}

}