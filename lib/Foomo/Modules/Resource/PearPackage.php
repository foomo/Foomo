<?php

/*
 * bestbytes-copyright-placeholder
 */

namespace Foomo\Modules\Resource;

use Foomo\Modules\Resource;

/**
 * pear package
 * @todo somebody needs to poke into pear registry ...
 */
class PearPackage extends Resource {

	/**
	 * @var string
	 */
	public $name;
	/**
	 * @var string
	 */
	public $apiVersion;
	public static function getResource($name, $apiVersion = null)
	{
		$ret = new self;
		$ret->name = $name;
		$ret->apiVersion = $apiVersion;
		return $ret;
	}
	public function resourceValid()
	{
		return class_exists($this->name);
	}

	public function resourceStatus()
	{
		return  'Pear package ' . $this->name . ' ' . ($this->resourceValid()?'is installed':'missing');
	}

	public function tryCreate()
	{
		return 'use the pear command: pear install ' . $this->name;
	}
	
}