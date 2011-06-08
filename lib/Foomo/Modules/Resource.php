<?php

/*
 * bestbytes-copyright-placeholder
 */

namespace Foomo\Modules;

/**
 * if you module needs a resource extend this class be our guest
 */
abstract class Resource {
	public $isNiceToHave = false;
	/**
	 * @todo discuss naming
	 * @internal
	 * @var boolean
	 */
	public $isRequired = false;
	/**
	 * chaining config
	 * @todo discuss this
	 * 
	 * @return Foomo\Modules\Resource
	 */
	public function isRequired($required = true)
	{
		$this->isRequired = $required;
		return $this;
	}
	/**
	 * mark as nice to have
	 * 
	 * @param type $isNiceToHave 
	 * 
	 * @return Foomo\Modules\Resource
	 */
	public function isNiceToHave($isNiceToHave = true)
	{
		$this->isNiceToHave = $isNiceToHave;
		return $this;
	}
	/**
	 * check if the resource is valid
	 *
	 * @return boolean
	 */
	abstract public function resourceValid();

	/**
	 * tell sth. human readable about the status of the resource
	 * 
	 * @return string
	 */
	abstract public function resourceStatus();

	/**
	 * try to create the resource
	 * 
	 * @return string a report of what happened
	 */
	abstract public function tryCreate();
}