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
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 * @author franklin <franklin@weareinteractive.com>
 */
class View
{
	//---------------------------------------------------------------------------------------------
	// ~ Variables
	//---------------------------------------------------------------------------------------------

	/**
	 * current exception
	 *
	 * @var Exception
	 */
	protected $exception;
	/**
	 * model view
	 *
	 * @var mixed
	 */
	protected $model;
	/**
	 * template
	 *
	 * @var Template
	 */
	protected $template;

	//---------------------------------------------------------------------------------------------
	// ~ Constructor
	//---------------------------------------------------------------------------------------------

	public function __construct(Template $template, $model = null, \Exception $exception = null)
	{
		$this->exception = $exception;
		$this->model = $model;
		$this->template = $template;
	}

	//---------------------------------------------------------------------------------------------
	// ~ Magic methods
	//---------------------------------------------------------------------------------------------

	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->render();
	}

	//---------------------------------------------------------------------------------------------
	// ~ Public methods
	//---------------------------------------------------------------------------------------------

	/**
	 * @param array $variables
	 * @return string
	 */
	public function render($variables=array())
	{
		return $this->template->render($this->model, $this, $this->exception, $variables);
	}

	/**
	 * include a php from a template
	 *
	 * @param string $include relative path to the parent template
	 * @param array $variablesToExtract attention this is experimental ... hash of variables that shall be extracted into the included templates scope
	 */
	public function includePhp($include, $variablesToExtract = array())
	{
		$view = $this;
		$model = $this->model;
		extract($variablesToExtract);
		$includeFile = dirname($this->template->file) . '/' . $include;
		if (file_exists($includeFile)) {
			include($includeFile);
		}
	}

	/**
	 * Indent lines by given value
	 *
	 * @param string $lines lines to indent
	 * @param int $indent number of indents
	 * @return string indented lines
	 */
	public function indent($lines, $indent=1)
	{
		$output = array();
		$lines = explode(PHP_EOL, $lines);
		foreach($lines as $line) $output[] = str_repeat(chr(9), $indent) . $line;
		return implode(PHP_EOL, $output);
	}

	/**
	 * escape from XSS
	 *
	 * @param string $string untrusted data
	 * @return string escaped / sanitized string
	 */
	public function escape($string)
	{
		return htmlspecialchars($string);
	}

	//---------------------------------------------------------------------------------------------
	// ~ Public static methods
	//---------------------------------------------------------------------------------------------

	/**
	 * get a view instance the lazy way
	 *
	 * @param string $templateFile
	 * @param mixed $model what ever the model may be
	 *
	 * @return Foomo\View
	 */
	public static function fromFile($templateFile, $model=null)
	{
		if (file_exists($templateFile)) {
			return new self(new Template(basename($templateFile), $templateFile), $model);
		} else {
			trigger_error('Template file ' . $templateFile . ' for view does not exist!', E_USER_WARNING);
			return null;
		}
	}
}