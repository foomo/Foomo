<?php

namespace Foomo\Modules\Resource;

class Module extends \Foomo\Modules\Resource {
	/**
	 * @var string 
	 */
	public $name;
	/**
	 * @var string
	 */
	public $version;
	/**
	 *
	 * @param string $name
	 * @param string $version
	 * 
	 * @return Foomo\Modules\Resource\Module
	 */
	public static function getResource($name, $version)
	{
		$ret = new self;
		$ret->name = $name;
		$ret->version = $version;
		return $ret;
	}
	public function resourceValid()
	{
		$installedVersion = \Foomo\Modules\Manager::getModuleVersion($this->name);
		if($installedVersion) {
			// var_dump($this->name, version_compare($this->version, $installedVersion, '>='));
			if(version_compare($installedVersion, $this->version) === -1) {
				return false;
			} else {
				return true;
			}
		} else {
			return false;
		}
	}

	public function resourceStatus()
	{
		if(in_array($this->name, \Foomo\Modules\Manager::getAvailableModules())) {
			if($this->resourceValid()) {
				return 'Module ' . $this->name . ' is available in required version ' . $this->version;
			} else {
				return 'Module ' . $this->name . ' has to be version >= ' . $this->version .' but is only ' . \Foomo\Modules\Manager::getModuleVersion($this->name);
			}
		} else {
			return 'Module ' . $this->name . ' is not available';
		}
	}

	public function tryCreate()
	{
		return 'can not create a module or upgrade it ;)';
	}
	
}