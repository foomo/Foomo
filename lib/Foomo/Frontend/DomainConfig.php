<?php

namespace Foomo\Frontend;

class DomainConfig extends \Foomo\Config\AbstractConfig
{
	//---------------------------------------------------------------------------------------------
	// ~ Constants
	//---------------------------------------------------------------------------------------------

	const NAME = 'Foomo.Frontend.navigation';

	//---------------------------------------------------------------------------------------------
	// ~ Variables
	//---------------------------------------------------------------------------------------------

	/**
	 * @var array
	 */
	public $menu = array(
		'Root.Configuration' => array('name' => 'Configuration', 'module' => 'Foomo', 'app' => 'Foomo\\Config\\Frontend', 'action' => 'config', 'target' => '_self'),
		'Root.Modules' => array('name' => 'Modules', 'module' => 'Foomo', 'app' => 'Foomo\\Modules\\Frontend', 'action' => 'modules', 'target' => '_self'),
		'Root.Log' => array('name' => 'Log', 'module' => 'Foomo', 'app' => 'Foomo\\Log\\Frontend', 'action' => 'log', 'target' => '_self'),
		'Root.Auth'	=> array('name' => 'Auth', 'module' => 'Foomo', 'app' => 'Foomo\\BasicAuth\\Frontend', 'action' => 'basicAuth', 'target' => '_self'),
		'Root.Cache' => array('name' => 'Cache', 'module' => 'Foomo', 'app' => 'Foomo\\Cache\\Frontend', 'action' => 'cache', 'target' => '_self')
	);

	//---------------------------------------------------------------------------------------------
	// ~ Public static methods
	//---------------------------------------------------------------------------------------------

	/**
	 * @param array $leaf
	 * @param array $paths
	 * @param array $link
	 */
	public static function toLeaf(array &$leaf, array $paths, array $link)
	{
		$pathName = array_shift($paths);

		if (!isset($leaf[$pathName])) {
			$leaf[$pathName] = array('name' => $pathName, 'active' => false, 'link' => null, 'leaves' => array());
		}

		if (count($paths) > 0) {
			self::toLeaf($leaf[$pathName]['leaves'], $paths, $link);
		} else {
			$leaf[$pathName]['link'] = $link;
			$leaf[$pathName]['link']['app'] = str_replace('\\', '.', $leaf[$pathName]['link']['app']);
		}
	}
}