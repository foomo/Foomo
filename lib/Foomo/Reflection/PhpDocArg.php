<?php

/*
 * bestbytes-copyright-placeholder
 */

namespace Foomo\Reflection;

/**
 * object representation of a parm doc comment
 */
class PhpDocArg {

	/**
	 * @var string
	 */
	public $name;
	/**
	 * @var string
	 */
	public $type;
	/**
	 * @var string
	 */
	public $comment;
	public function __construct($name, $type, $comment = '')
	{
		$this->name = $name;
		$this->type = $type;
		$this->comment = $comment;
	}

}
