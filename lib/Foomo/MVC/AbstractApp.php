<?php

/*
 * bestbytes-copyright-placeholder
 */

namespace Foomo\MVC;

/**
 * extend this to get MVC apps and overwrite the constructor, if you need special treatment for the model like session persistance
 */
abstract class AbstractApp {

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
	 * @var Foomo\MVC\View
	 */
	public $view;
	public function __construct($appClassName = null)
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