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

/**
 * extend this to get MVC apps and overwrite the constructor, if you need special treatment for the model like session persistance
 * 
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
abstract class AbstractApp
{
	//---------------------------------------------------------------------------------------------
	// ~ Variables
	//---------------------------------------------------------------------------------------------

	/**
	 * @var stdClass
	 */
	public $model;
	/**
	 * @var stdClass
	 */
	public $controller;
	/**
	 * view
	 *
	 * @var View
	 */
	public $view;

	//---------------------------------------------------------------------------------------------
	// ~ Constructor
	//---------------------------------------------------------------------------------------------

	/**
	 * @param string $appClassName
	 */
	public function __construct($appClassName=null)
	{
		if (!$appClassName) {
			$appClassName = get_class($this);
		}
		if (!$this->controller) {
			$controllerClassName = $this->getClassName($appClassName, 'Controller');
			$this->controller = new $controllerClassName;
		}
		if (!$this->model) {
			$modelClassName = $this->getClassName($appClassName, 'Model');
			$this->model = new $modelClassName;
		}
		$this->controller->model = $this->model;
	}

	//---------------------------------------------------------------------------------------------
	// ~ Private methods
	//---------------------------------------------------------------------------------------------

	/**
	 * supported layouts
	 *
	 *   very old school:
	 *     Foo
	 *     FooController
	 *     FooModel
	 *
	 *   NS old school:
	 *     Foo\App
	 *     Foo\Model
	 *     Foo\
	 *
	 *   NS new school:
	 *     Foo
	 *     Foo\Model
	 *     Foo\Controller
	 *
	 * @param type $appClassName
	 * @param type $type
	 * @return type
	 */
	private function getClassName($appClassName, $type)
	{
		// very old school
		$candidate = $appClassName . $type;
		if (!@class_exists($candidate) && strpos($appClassName, '\\') !== false) {
			// old school
			$allClassParts = $classParts = explode('\\', $appClassName);
			array_pop($classParts);
			$classParts[] = $type;
			$simpleCandidate = $candidate;
			$oldCandidate = $candidate = implode('\\', $classParts);
			if (!class_exists($candidate)) {
				// new school
				$newCandidate = $candidate = implode('\\', $allClassParts) . '\\' . $type;
				if(!class_exists($candidate)) {
					// giving up
					trigger_error(
						'could not determine ' . $type .
						' for ' . $appClassName .
						' I was looking for ' . $simpleCandidate .
						' or ' . $oldCandidate .
						' or ' . $newCandidate
						, E_USER_ERROR
					);
				}
			}
		}
		return $candidate;
	}
}