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
 * yet another template engine
 *
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 * @internal
 */
class Template {
	static $debug = false;
	static $debugTemplate = '<div style="border:red 3px solid !important; margin: 5px !important;"><div style="background-color: red !important; color: white !important; padding: 3px !important;">$file</div>$rendering</div>';
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
	 * @param \Foomo\View $view
	 * @param \Exception $exception
	 * @param array $variables
	 *
	 * @return string
	 */
	public function render($model = null, View $view = null, \Exception $exception = null, array $variables = array())
	{
		ob_start();
		$this->run($model, $view, $exception, $variables);
		$rendering = ob_get_clean();
		if(self::$debug) {
			if(substr($this->file, 0, strlen(CORE_CONFIG_DIR_MODULES)) == CORE_CONFIG_DIR_MODULES) {
				$file = substr($this->file, strlen(CORE_CONFIG_DIR_MODULES) + 1);
				$fileParts = explode(DIRECTORY_SEPARATOR, $file);
				if($fileParts[1] == 'views') {
					$file = $fileParts[0] . '::' . implode(DIRECTORY_SEPARATOR, array_slice($fileParts, 2));
				}
			} else {
				$file = $this->file;
			}
			return str_replace(array('$file', '$rendering'), array($file, $rendering), self::$debugTemplate);
		} else {
			return $rendering;
		}
	}

	private function run($model, $view, $exception, $variables)
	{
		extract($variables);
		self::$stack[] = $this->file;
		include $this->file;
		array_pop(self::$stack);
	}

}
