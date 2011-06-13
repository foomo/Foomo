<?php

/*
 * bestbytes-copyright-placeholder
 */

namespace Foomo\MVC;

use Foomo\Template;
use Foomo\MVC;
use Foomo\Timer;

/**
 * MVC version of Foomo\View
 */
class View extends \Foomo\View {

	public static $trackPartials = false;
	/**
	 * my app
	 * 
	 * @var Foomo\MVC\AbstractApp
	 */
	private $app;
	/**
	 * my url handler
	 *
	 * @var Foomo\MVC\URLHandler
	 */
	private $handler;
	public $path;
	public $exception;
	/**
	 * current action
	 *
	 * @var string
	 */
	public $currentAction;
	/**
	 * current parameters
	 * 
	 * @var array
	 */
	public $currentParameters;
	public static $viewStack = array();

	public function __construct(AbstractApp $app, URLHandler $handler, Template $template, Exception $exception = null)
	{
		$this->currentAction = $handler->lastAction;
		$this->currentParameters = $handler->lastParameters;
		$this->app = $app;
		$this->handler = $handler;
		$this->path = $this->handler->path;
		parent::__construct($template, $this->app->model, $exception);
	}

	public function render($variables = array())
	{
		//return '<div style="border:1px red solid;padding-left:30px;"><h1>' . $this->escape($this->path) . '</h1>' . parent::render().'</div>';
		return parent::render($variables);
	}

	/**
	 * escape from XSS
	 * 
	 * @param string $string untrusted data
	 * 
	 * @return string escaped / sanitized string
	 */
	public function escape($string)
	{
		return htmlspecialchars($string);
	}

	/**
	 * get a link to a method on my controller
	 *
	 * @param string $methodName name of the method
	 * @param array $parameters paramters in an array
	 *
	 * @return string
	 */
	public function url($methodName = 'default', $parameters = array())
	{
		return $this->handler->renderUrl(get_class($this->app->controller), $methodName, $parameters);
	}

	/**
	 * 
	 * create a html link (or a text if the link is active)
	 * 
	 * @param string $linkText text to display on the link
	 * @param string $methodName method to call
	 * @param array $parameters parameters
	 * @param string $title title attribute in the link
	 * @param string $target where to link to
	 * 
	 * @return string <a href="/bla/blubb/parmOne/parmTwo" title="title - looks like a tooltip" target="_self">link text</a>
	 */
	public function link($linkText, $methodName = 'default', $parameters = array(), $title = null, $target = '_self', $name = '')
	{
		$methodMatch = $this->currentAction == $methodName || $this->currentAction == 'action' . ucfirst($methodName);
		//var_dump(array('parms' => $parameters, 'currentParms' => $this->currentParameters));
		//if(!$methodMatch || $this->parameterMatch($parameters, $this->currentParameters)) {// $parameters != $this->currentParameters) {
		if (!$methodMatch || !$this->parameterMatch($parameters, $this->currentParameters)) {// $parameters != $this->currentParameters) {
			$ret =
					'<a href="' . \htmlspecialchars($this->url($methodName, $parameters)) . '"' .
					( ($target = !'_self') ? ' target="' . $target . '"' : '' ) .
					( $name ? ' name="' . \htmlspecialchars($name) . '"' : '')
			;
			if ($title) {
				$ret .= ' title="' . $this->escape($title) . '"';
			}
			$ret .= '>' . $this->escape($linkText) . '</a>';
		} else {
			$ret = $this->escape($linkText);
		}
		return $ret;
	}

	private function parameterMatch($parmsA, $parmsB)
	{
		return $this->trimParms($parmsA) == $this->trimParms($parmsB);
	}

	private function trimParms($parms)
	{
		$ret = array();
		foreach ($parms as $parm) {
			if (!is_null($parm)) {
				$ret[] = $parm;
			}
		}
		return $ret;
	}

	/**
	 * render a partial
	 *
	 * @param string $name name of the action
	 * @return string partial output
	 */
	public function partial($name, $variables = array())
	{
		static $level = -1;
		if (self::$trackPartials) {
			Timer::start($topic = __METHOD__ . ' ' . get_class($this->app) . ' ' . $name);
		}
		$template = MVC::getViewPartialTemplate(get_class($this->app), $name);
		$view = new self($this->app, $this->handler, $template);
		$level++;
		$rendering = $view->render($variables);
		// catch partial content
		if (MVC::$catchingViews) {
			MVC::catchPartial($name, $level, $rendering);
		}
		$level--;
		if (self::$trackPartials) {
			Timer::stop($topic);
		}
		return $rendering;
	}

	protected $_localeChain;

	/**
	 * break the locale chains defind by $_SERVER['HTTP_ACCEPT_LANGUAGE']
	 *
	 * @param string $localeChain
	 */
	public function setLocaleChain($localeChain)
	{
		$this->_translation = null;
		$this->_localeChain = $localeChain;
	}

	/**
	 * get the locale chain
	 * 
	 * @return array
	 */
	public function getLocaleChain()
	{
		return $this->_localeChain;
	}

	/**
	 * a locale
	 *
	 * @var Foomo\Translation
	 */
	protected $_translation;

	public function _($msgId, $msgPluralId = null, $count = null)
	{
		if (!$this->_translation) {
			$appClassName = get_class($this->app);
			$this->_translation = new \Foomo\Translation(MVC::getLocaleRoots($appClassName), self::getNamespace($appClassName), $this->_localeChain);
		}
		return $this->_translation->_($msgId, $msgPluralId, $count);
	}
	private function getNamespace($className)
	{
		$parts = explode('\\', $className);
		if($parts[count($parts)-1] != 'Frontend') {
			array_pop($parts);
		}
		return implode('\\', $parts);
	}
	public function addResources(array $resources)
	{
		
	}
	
	public function addResource(Resource $res)
	{
		
	}

}