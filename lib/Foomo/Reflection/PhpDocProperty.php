<?php

/*
 * bestbytes-copyright-placeholder
 */

namespace Foomo\Reflection;

/**
 * for properties described by @property
 */
class PhpDocProperty {

	/**
	 * name of the property
	 *
	 * @var string
	 */
	public $name;
	/**
	 * type of the property
	 *
	 * @var string
	 */
	public $type;
	/**
	 * comment
	 *
	 * @var string
	 */
	public $comment;
	/**
	 * true if readable
	 *
	 * @var boolean
	 */
	public $read = true;
	/**
	 * true if writable
	 *
	 * @var boolean
	 */
	public $write = true;
	public function __construct($name, $type, $comment, $read = true, $write = true)
	{
		$this->name = $name;
		$this->type = $type;
		$this->comment = $comment;
		$this->read = $read;
		$this->write = $write;
	}

}