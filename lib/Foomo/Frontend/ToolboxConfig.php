<?php

namespace Foomo\Frontend;

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
		'Root.Path' => array('name' => 'Name', 'module' => 'My.Module', 'app' => 'My\\Module\\Frontend', 'action' => 'default', 'target' => '_self')
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
	 * @param array $link
	 */
	public static function toLeaf(array &$leaf, array $paths, $menuEntry)
	{
		$pathName = array_shift($paths);

		if (!isset($leaf[$pathName])) {
			$leaf[$pathName] = array('name' => $pathName, 'active' => false, 'link' => null, 'leaves' => array());
		}

		if (count($paths) > 0) {
			self::toLeaf($leaf[$pathName]['leaves'], $paths, $menuEntry);
		} else {
			$leaf[$pathName]['link'] = $menuEntry->toArray();
			$leaf[$pathName]['link']['app'] = str_replace('\\', '.', $leaf[$pathName]['link']['app']);
		}
	}
}