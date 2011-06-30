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
 * @author franklin <franklin@weareinteractive.com>
 */
class ToolboxConfig extends \Foomo\Config\AbstractConfig
{
	//---------------------------------------------------------------------------------------------
	// ~ Constants
	//---------------------------------------------------------------------------------------------

	const NAME = 'Foomo.Frontend.toolboxConfig';

	//---------------------------------------------------------------------------------------------
	// ~ Variables
	//---------------------------------------------------------------------------------------------

	/**
	 * @var array
	 */
	public $menu = array(
		'Root.Path' => array('name' => 'Name', 'module' => 'My.Module', 'app' => 'My.Module.Frontend', 'action' => 'default', 'target' => '_self')
	);

	//---------------------------------------------------------------------------------------------
	// ~ Public static methods
	//---------------------------------------------------------------------------------------------

	/**
	 * @return Foomo\Frontend\ToolboxConfig\MenuEntry[]
	 */
	public function getMenuEntries()
	{
		$entries = array();
		foreach ($this->menu as $path => $entry) $entries[] = $this->getMenuEntry($path);
		return $entries;
	}

	/**
	 * @return Foomo\Frontend\ToolboxConfig\MenuEntry
	 */
	public function getMenuEntry($path)
	{
		$entry = $this->menu['path'];
		return ToolboxConfig\MenuEntry::create($path, $entry['name'], $entry['module'], $entry['app'], $entry['action'], $entry['target']);
	}

	/**
	 * @param array $leaf
	 * @param array $paths
	 * @param Foomo\Frontend\ToolboxConfig\MenuEntry $menuEntry
	 */
	public static function toLeaf(array &$leaf, array $paths, $menuEntry)
	{
		$pathName = array_shift($paths);
		if (!isset($leaf[$pathName])) $leaf[$pathName] = array('name' => $pathName, 'active' => false, 'link' => null, 'leaves' => array());

		if (count($paths) > 0) {
			self::toLeaf($leaf[$pathName]['leaves'], $paths, $menuEntry);
		} else {
			if (is_null($menuEntry->app)) return;
			$leaf[$pathName]['link'] = $menuEntry->toArray();
			$leaf[$pathName]['link']['app'] = str_replace('\\', '.', $leaf[$pathName]['link']['app']);
		}
	}
}