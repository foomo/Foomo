<?php

/*
 * This file is part of the foomo Opensource Framework.
 * 
 * The foomo Opensource Framework is free software: you can redistribute it
 * and/or modify it under the terms of the GNU Lesser General Public License as
 * published  by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 * 
 * The foomo Opensource Framework is distributed in the hope that it will
 * be useful, but WITHOUT ANY WARRANTY; without even the implied warranty
 * of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public License along with
 * the foomo Opensource Framework. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Foomo\MVC;
 
use Foomo\Modules\Resource\Fs;
use Foomo\Modules\Manager;

/**
 * Create MVC apps
 * 
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author Jan Halfar jan@bestbytes.com
 */
class Scaffolder
{
	/**
	 * create an app in a namespace in a module
	 * 
	 * @param string $module
	 * @param string $namespace
	 * @param string $author
	 * 
	 * @throws \Exception 
	 */
	public static function createApp($module, $namespace, $author)
	{
		if(self::appExists($namespace)) {
			throw new \Exception('app already exists');
		}
		if(empty($namespace)) {
			throw new \Exception('namespace was not set');
		}
		if(!in_array($module, Manager::getEnabledModules())) {
			throw new \Exception('module ' . $module . ' not enabled');
		}
		if(substr($namespace, 0, 1) == '\\') {
			// strip a prepended \ if there
			$namespace = substr($namespace, 1);
		}
		// frontend namespace
		$namespaceArray = explode('\\', $namespace);
		$namespaceArray[] = 'Frontend';
		
		// create folders		
		$dirLib = self::createAndGetModuleNamespaceDir($module, $namespaceArray, 'lib');
		$dirLocale = self::createAndGetModuleNamespaceDir($module, $namespaceArray, 'locale');
		$dirViews = self::createAndGetModuleNamespaceDir($module, $namespaceArray, 'views');
		foreach(array('partials') as $viewFolder) {
			self::createAndGetModuleNamespaceDir($module, array_merge($namespaceArray, array($viewFolder)), 'views');
		}
		$dirTests = self::createAndGetModuleNamespaceDir($module, $namespaceArray, 'tests');
		
		// write files
		$templateRoot = \Foomo\Module::getViewsDir(implode(DIRECTORY_SEPARATOR, array('Foomo', 'MVC', 'scaffolds')));
		
		$model = array(
			'namespace' => implode('\\', $namespaceArray), 
			'author' =>$author,
			'module' => $module
		);

		// write locale
		$locales = array('de', 'en');
		foreach($locales as $locale) {
			$localeFile = $dirLocale->getFileName() . DIRECTORY_SEPARATOR . $locale . '.yml';
			if(!file_exists($localeFile)) {
				file_put_contents($localeFile, 'LOCALE: ' . $locale);
			} else {
				throw new \Exception('locale already exists');
			}
		}
		// write classes
		// Model, Controller
		foreach(array('Model' => 'model.tpl', 'Controller' => 'controller.tpl') as $className => $templateName) {
			self::writeCode(
				$templateRoot . DIRECTORY_SEPARATOR . $templateName,
				$dirLib->getFileName() . DIRECTORY_SEPARATOR . $className . '.php',
				$model
			);
		}
		// write default view
		self::writeCode(
			$templateRoot . DIRECTORY_SEPARATOR . 'defaultView.tpl', 
			$dirViews->getFileName() . DIRECTORY_SEPARATOR . 'default.tpl',
			$model
		);
		// write test for the controller
		// ControllerTest
		self::writeCode(
			$templateRoot . DIRECTORY_SEPARATOR . 'controllerTest.tpl', 
			$dirTests->getFileName() . DIRECTORY_SEPARATOR . 'ControllerTest.php',
			$model
		);
		
		// Frontend
		$model['namespace'] = implode('\\', array_slice($namespaceArray, 0, -1));
		self::writeCode(
			$templateRoot . DIRECTORY_SEPARATOR . 'frontend.tpl', 
			dirname($dirLib->getFileName()) . DIRECTORY_SEPARATOR . 'Frontend.php',
			$model
		);
	}
	/**
	 * try to create and get it
	 * 
	 * @param string $module
	 * @param array $namespaceArray
	 * @param string $type
	 * 
	 * @return Fs
	 */
	private static function createAndGetModuleNamespaceDir($module, $namespaceArray, $type)
	{
		$moduleRootDir = \Foomo\Config::getModuleDir($module);
		// create folders		
		$res = Fs::getAbsoluteResource(
			Fs::TYPE_FOLDER, 
			$moduleRootDir . DIRECTORY_SEPARATOR . $type . DIRECTORY_SEPARATOR . 
			implode(DIRECTORY_SEPARATOR, $namespaceArray))
		;
		$res->tryCreate();
		return $res;
	}
	private function writeCode($template, $filename, $model)
	{
		$model = (object) $model;
		$view = new \Foomo\View(new \Foomo\Template('sepp', $template), $model);
		if(!file_exists($filename)) {
			file_put_contents($filename, $view->render());
		} else {
			throw new \Exception('did not write ' . $filename . ', because it already exists');
		}
	}
	private static function appExists($namespace)
	{
		$className = $namespace . '\\Frontend';
		foreach(array_values(AppDirectory::getAppClassDirectory()) as $existingClassName) {
			if($existingClassName == $className) {
				return true;
			}
		}
		return false;
	}
}