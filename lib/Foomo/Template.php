<?php

/*
 * bestbytes-copyright-placeholder
 */

namespace Foomo;

/**
 * yet another template engine
 *
 * @internal
 */
class Template {

	/**
	 * names make things friends
	 *
	 * @var string
	 */
	private $name;
	/**
	 * template file name
	 *
	 * @var string
	 */
	public $file;
	/**
	 *
	 *
	 * @var string
	 */
	private $buffer;
	public static $stack = array();

	/**
	 * @param string $name name of the template
	 * @param string $file filename of the template
	 *
	 */
	public function __construct($name, $file)
	{
		$this->name = $name;
		$this->file = $file;
		if (!file_exists($file)) {
			trigger_error('template file does not exist >' . $file . '<', E_USER_WARNING);
		}
	}

	/**
	 * render the template => apply the data
	 *
	 * @param mixed $model
	 * @param Foomo\View $view
	 * @param Exception $exception
	 * @param array $variables
	 *
	 * @return string
	 */
	public function render($model = null, View $view = null, \Exception $exception = null, array $variables = array())
	{
		ob_start(array($this, 'handle'));
		$this->run($model, $view, $exception, $variables);
		ob_end_clean();
		return $this->buffer;
	}

	public function handle($buffer)
	{
		$this->buffer .= $buffer;
		return '';
	}

	private function run($model, $view, $exception, $variables)
	{
		extract($variables);
		self::$stack[] = $this->file;
		include $this->file;
		array_pop(self::$stack);
	}

}
