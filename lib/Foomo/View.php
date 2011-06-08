<?php

/*
 * bestbytes-copyright-placeholder
 */

namespace Foomo;

use Exception;

/**
 * yet another view
 */
class View {

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
	public function __construct(Template $template, $model = null, Exception $exception = null)
	{
		$this->exception = $exception;
		$this->model = $model;
		$this->template = $template;
	}

	public function __toString()
	{
		return $this->render();
	}

	public function render($variables = array())
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
	 * get a view instance the lazy way
	 *
	 * @param string $templateFile
	 * @param mixed $model what ever the model may be
	 * 
	 * @return Foomo\View
	 */
	public static function fromFile($templateFile, $model = null)
	{
		if(file_exists($templateFile)) {
			return new self(new Template(basename($templateFile), $templateFile), $model);
		} else {
			return null;
		}
	}

}
