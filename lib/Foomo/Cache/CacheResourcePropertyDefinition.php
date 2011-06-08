<?php

/*
 * bestbytes-copyright-placeholder
 */

namespace Foomo\Cache;

class CacheResourcePropertyDefinition {

	public $name;
	public $type = 'string';
	public $defaultValue = null;
	public $isOptional = false;

	public function __construct(\ReflectionParameter $paramRefl, \Foomo\Reflection\PhpDocEntry $phpDoc)
	{
		$this->name = $paramRefl->getName();
		$this->isOptional = $paramRefl->isOptional();
		if ($this->isOptional) {
			$this->defaultValue = $paramRefl->getDefaultValue();
		}
		/* @var $docParam Foomo\Reflection\PhpDocArg */
		foreach ($phpDoc->parameters as $docParam) {
			if ($docParam->name == $paramRefl->getName()) {
				$this->type = $docParam->type;
				break;
			}
		}
		if (!$this->type) {
			$this->type = 'string';
		}
	}

	/**
	 * check if the type is an array (of)
	 * 
	 * @return boolean
	 */
	public function typeIsArray()
	{
		if ($this->type == 'array') {
			return true;
		} else {
			if (strpos($this->type, '[]') == \strlen($this->type) - 2) {
				return true;
			} else {
				return false;
			}
		}
	}

}