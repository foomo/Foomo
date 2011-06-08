<?php

/*
 * bestbytes-copyright-placeholder
 */

namespace Foomo\Modules\Resource;

use Foomo\Modules\Resource;

/**
 * foomo php module requirement
 */
class PhpModule extends Resource {
	/**
	 * @var string
	 */
	public $name;
	public static function getResource($name)
	{
		$ret = new self;
		$ret->name = $name;
		return $ret;
	}
	public function resourceValid()
	{
		return in_array($this->name, get_loaded_extensions());
	}

	public function resourceStatus()
	{
		return  'required php module ' . $this->name . ' was ' . ($this->resourceValid()?'loaded':'not loaded');
	}

	public function tryCreate()
	{
		return 'can not create a php modules';
	}
}