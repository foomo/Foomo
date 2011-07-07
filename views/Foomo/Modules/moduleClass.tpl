<?=  '<?php' . PHP_EOL; ?>

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

namespace <?= $model['namespace'] ?>;

/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 */
class Module extends \Foomo\Modules\ModuleBase
{
	//---------------------------------------------------------------------------------------------
	// ~ Constants
	//---------------------------------------------------------------------------------------------

	/**
	 * the name of this module
	 *
	 */
	const NAME = '<?= $model['name'] ?>';

	//---------------------------------------------------------------------------------------------
	// ~ Overriden static methods
	//---------------------------------------------------------------------------------------------

	/**
	 * Your module needs to be set up, before being used - this is the place to do it
	 */
	public static function initializeModule()
	{
	}

	/**
	 * Get a plain text description of what this module does
	 *
	 * @return string
	 */
	public static function getDescription()
	{
		return '<?= addcslashes($model['description'], "'") ?>';
	}

	/**
	 * get all the module resources
	 *
	 * @return Foomo\Modules\Resource[]
	 */
	public static function getResources()
	{
		return array(
<? foreach($model['dependencies'] as $dep): ?>
			\Foomo\Modules\Resource\Module::getResource('<?= $dep ?>', self::VERSION),
<? endforeach; ?>
			// get a run mode independent folder var/<runMode>/test
			// \Foomo\Modules\Resource\Fs::getVarResource(\Foomo\Modules\Resource\Fs::TYPE_FOLDER, 'test'),
			// and a file in it
			// \Foomo\Modules\Resource\Fs::getVarResource(\Foomo\Modules\Resource\Fs::TYPE_File, 'test' . DIRECTORY_SEPARATOR . 'someFile'),
			// request a cache resource
			// \Foomo\Modules\Resource\Fs::getCacheResource(\Foomo\Modules\Resource\Fs::TYPE_FOLDER, 'navigationLeaves'),
			// a database configuration
			// \Foomo\Modules\Resource\Config::getResource('yourModule', 'db')
		);
	}
}