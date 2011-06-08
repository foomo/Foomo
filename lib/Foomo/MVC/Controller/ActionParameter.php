<?php

/*
 * bestbytes-copyright-placeholder
 */

namespace Foomo\MVC\Controller;

class ActionParameter {

	/**
	 * @var string
	 */
	public $type = 'string';
	/**
	 * @var boolean
	 */
	public $optional = false;
	/**
	 * @var string
	 */
	public $name;
	private $value;
	public function __construct($value = null, $name = null, $type = 'string', $optional = false)
	{
		$this->name = $name;
		$this->value = $value;
		$this->type = $type;
		$this->optional = $optional;
	}

	public function setValue($newValue)
	{
		$this->value = $newValue;
	}

	public function getValue()
	{
		return $this->value;
	}

}