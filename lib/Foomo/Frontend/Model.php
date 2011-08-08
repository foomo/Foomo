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

namespace Foomo\Frontend;

/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 * @author franklin <franklin@weareinteractive.com>
 */
class Model
{
	//---------------------------------------------------------------------------------------------
	// ~ Variables
	//---------------------------------------------------------------------------------------------

	/**
	 * @var string
	 */
	public $currentFrameUrl = 'about:blank';
	/**
	 * @var string[]
	 */
	public $classMap;
	/**
	 * @var array
	 */
	public $navi = array();
	/**
	 * @var type
	 */
	public $currentModuleApp;

	//---------------------------------------------------------------------------------------------
	// ~ Constructor
	//---------------------------------------------------------------------------------------------

	/**
	 * @todo: cache
	 */
	public function __construct()
	{
		$this->buildNavi();
	}

	//---------------------------------------------------------------------------------------------
	// ~ Public methods
	//---------------------------------------------------------------------------------------------

	/**
	 * @todo make navi session persistent and use parameters
	 *
	 * @param string $app
	 * @param string $action
	 */
	public function updateNavi($app, $action, $parameters)
	{
		$this->navi['Root']['active'] = $this->isActiveLeaf($this->navi['Root'], $app, $action);
	}

	//---------------------------------------------------------------------------------------------
	// ~ Private methods
	//---------------------------------------------------------------------------------------------

	/**
	 * @param array $leaf
	 * @param string $app
	 * @param string $action
	 * @return boolean
	 */
	private function isActiveLeaf(&$leaf, $app, $action)
	{
		if (!is_null($leaf['link']) && $leaf['link']['app'] == $app && $leaf['link']['action'] == $action) {
			return true;
		} else if (!empty($leaf['leaves'])) {
			$active = false;
			foreach ($leaf['leaves'] as $key => $subLeaf) {
				$leaf['leaves'][$key]['active'] = $this->isActiveLeaf($leaf['leaves'][$key], $app, $action);
				if ($leaf['leaves'][$key]['active']) $active = true;
			}
			return $active;
		} else {
			return false;
		}
	}

	/**
	 *
	 */
	private function buildNavi()
	{
		$checkedModuleNames = array();
		$configuredModuleNames = array();
		foreach (\Foomo\AutoLoader::getClassesByInterface('Foomo\\Frontend\\ToolboxInterface') as $className) {
			$moduleName = \Foomo\Modules\Manager::getModuleByClassName($className);
			if (!in_array($moduleName, $checkedModuleNames) && null != $config = \Foomo\Config::getConf(str_replace('\\', '.', $moduleName), \Foomo\Frontend\ToolboxConfig::NAME)) {
				$menuEntries = $config->getMenuEntries();
				$configuredModuleNames[] = $moduleName;
			} else if (!in_array($moduleName, $configuredModuleNames) && class_exists($className)) {
				$menuEntries = $className::getMenu();
			}
			if (!in_array($moduleName, $checkedModuleNames)) $checkedModuleNames[] = $moduleName;
			if ($menuEntries) foreach ($menuEntries as $menuEntry) ToolboxConfig::toLeaf($this->navi, $menuEntry->path, $menuEntry);
		}
	}
}