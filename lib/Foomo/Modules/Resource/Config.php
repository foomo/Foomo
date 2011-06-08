<?php

/*
 * bestbytes-copyright-placeholder
 */

namespace Foomo\Modules\Resource;

/**
 * a config resource
 */
class Config extends \Foomo\Modules\Resource {

	/**
	 * module
	 *
	 * @var string
	 */
	public $module;
	/**
	 * config name
	 *
	 * @var string
	 */
	public $name;
	/**
	 * domain
	 *
	 * @var string
	 */
	public $subDomain;

	public function __construct($module, $domain, $subDomain = '')
	{
		$this->module = $module;
		$this->name = $domain;
		$this->subDomain = $subDomain;
	}

	/**
	 * get a config resource
	 * 
	 * @param string $module
	 * @param string $name
	 * @param string $subDomain
	 * 
	 * @return Foomo\Modules\Resources\Fs
	 */
	public static function getResource($module, $name, $subDomain = '')
	{
		return new self($module, $name, $subDomain);
	}

	public function resourceValid()
	{
		if (\Foomo\Config::confExists($this->module, $this->name, $this->subDomain)) {
			return true;
		} else {
			return false;
		}
	}

	public function resourceStatus()
	{
		if ($this->resourceValid()) {
			return 'Configuration for domain ' . $this->name . ($this->subDomain != '' ? '/' . $this->subDomain : '') . ' is ok';
		} else {
			$ret = 'you need to create a config for the module ' . $this->module . ' in the domain ' . $this->name;
			if (!empty($this->subDomain)) {
				return $ret .= ' and subDomain ' . $this->subDomain;
			}
			return $ret;
		}
	}

	public function tryCreate()
	{
		if (\Foomo\Config::confExists($this->module, $this->name, $this->subDomain)) {
			return 'config exists';
		} else {
			return 'created default config for ' . $this->module . ' - ' . $this->name . ' - ' . var_export(\Foomo\Config::getConf($this->module, $this->name, $this->subDomain), true);
		}
	}

}