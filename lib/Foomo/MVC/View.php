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

namespace Foomo\MVC;

use Foomo\Template;
use Foomo\MVC;
use Foomo\Timer;
use Exception;

/**
 * MVC version of Foomo\View
 *
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
class View extends \Foomo\View
{
	//---------------------------------------------------------------------------------------------
	// ~ Static variables
	//---------------------------------------------------------------------------------------------

	/**
	 * @var boolean
	 */
	public static $trackPartials = false;
	/**
	 * @var array
	 */
	public static $viewStack = array();

	//---------------------------------------------------------------------------------------------
	// ~ Variables
	//---------------------------------------------------------------------------------------------

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
	/**
	 * a locale
	 *
	 * @var Foomo\Translation
	 */
	private $translation;
	/**
	 * @var string
	 */
	private $localeChain;
	/**
	 * @var string
	 */
	public $path;
	/**
	 *
	 * @var Exception
	 */
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

	//---------------------------------------------------------------------------------------------
	// ~ Constructor
	//---------------------------------------------------------------------------------------------

	/**
	 * @param Foomo\MVC\AbstractApp $app
	 * @param Foomo\MVC\URLHandler $handler
	 * @param Foomo\Template $template
	 * @param \Exception $exception
	 */
	public function __construct(AbstractApp $app, URLHandler $handler, Template $template, \Exception $exception=null)
	{
		$this->currentAction = $handler->lastAction;
		$this->currentParameters = $handler->lastParameters;
		$this->app = $app;
		$this->handler = $handler;
		$this->path = $this->handler->path;
		parent::__construct($template, $this->app->model, $exception);
	}

	//---------------------------------------------------------------------------------------------
	// ~ Public methods
	//---------------------------------------------------------------------------------------------

	/**
	 * @param array $variables
	 * @return string
	 */
	public function render($variables = array())
	{
		return parent::render($variables);
	}

	/**
	 * get a link to a method on my controller
	 *
	 * @param string $methodName name of the method
	 * @param array $parameters paramters in an array
	 * @return string
	 */
	public function url($methodName='default', $parameters=array())
	{
		return $this->handler->renderUrl(get_class($this->app->controller), $methodName, $parameters);
	}

	/**
	 * create a html link (or a text if the link is active)
	 * @todo: refactor title, target, links to attributes to be able to set ie class
	 *
	 * @param string $linkText text to display on the link
	 * @param string $methodName method to call
	 * @param array $parameters parameters
	 * @param array $attributes array('name' => 'value')
	 * @return string <a href="/bla/blubb/parmOne/parmTwo" title="title - looks like a tooltip" target="_self">link text</a>
	 */
	public function link($linkText, $methodName='default', array $parameters=array(), array $attributes=array())
	{
		$attributes = array_merge(array('target' => '_self'), $attributes);
		$methodMatch = ($this->currentAction == $methodName || $this->currentAction == 'action' . ucfirst($methodName));
		if (!$methodMatch || !$this->parameterMatch($parameters, $this->currentParameters)) {
			$ret = '<a href="' . \htmlspecialchars($this->url($methodName, $parameters)) . '"';
			foreach ($attributes as $name => $value) $ret .= ' ' . $name . '="' . \htmlspecialchars($value) . '"';
			$ret .= '>' . $this->escape($linkText) . '</a>';
		} else {
			$ret = $this->escape($linkText);
		}
		return $ret;
	}

	/**
	 * render a partial
	 *
	 * @param string $name name of the action
	 * @return string partial output
	 */
	public function partial($name, $variables = array(), $class = '')
	{
		static $level = -1;
		if (self::$trackPartials) {
			Timer::start($topic = __METHOD__ . ' ' . get_class($this->app) . ' ' . $name);
		}

		if(!$class){
			$class = get_class($this->app);
		}
		//trigger_error("-------------->".get_class($this->app));
		$template = MVC::getViewPartialTemplate($class, $name);
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

	/**
	 * @todo: implement
	 *
	 * @param Foomo\MVC\View\Resource[] $resources
	 */
	public function addResources(array $resources)
	{

	}

	/**
	 * @todo: implement
	 *
	 * @param Foomo\MVC\View\Resource $res
	 */
	public function addResource(\Foomo\MVC\View\Resource $res)
	{
	}

	/**
	 * break the locale chains defind by $_SERVER['HTTP_ACCEPT_LANGUAGE']
	 *
	 * @param string $localeChain
	 */
	public function setLocaleChain($localeChain)
	{
		$this->translation = null;
		$this->localeChain = $localeChain;
	}

	/**
	 * get the locale chain
	 *
	 * @return array
	 */
	public function getLocaleChain()
	{
		return $this->localeChain;
	}

	/**
	 * translate string
	 *
	 * @param string $msgId
	 * @param string $msgPluralId
	 * @param integer $count
	 * @return string translated string
	 */
	public function _($msgId, $msgPluralId = null, $count = null)
	{
		if (!$this->translation) {
			$appClassName = get_class($this->app);
			$this->translation = new \Foomo\Translation(MVC::getLocaleRoots($appClassName), self::getNamespace($appClassName), $this->localeChain);
		}
		return $this->translation->_($msgId, $msgPluralId, $count);
	}

	//---------------------------------------------------------------------------------------------
	// ~ Private methods
	//---------------------------------------------------------------------------------------------

	/**
	 * get php namespace of given class
	 *
	 * @param string $className
	 * @return string php namespace
	 */
	private function getNamespace($className)
	{
		$parts = explode('\\', $className);
		if ($parts[count($parts)-1] != 'Frontend') array_pop($parts);
		return implode('\\', $parts);
	}

	/**
	 *
	 * @param array $parmsA
	 * @param array $parmsB
	 * @return boolen parameters match
	 */
	private function parameterMatch($parmsA, $parmsB)
	{
		return ($this->trimParms($parmsA) == $this->trimParms($parmsB));
	}

	/**
	 * @param array $parms
	 * @return array
	 */
	private function trimParms($parms)
	{
		$ret = array();
		foreach ($parms as $parm) if (!is_null($parm)) $ret[] = $parm;
		return $ret;
	}
}