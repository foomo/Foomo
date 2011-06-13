<?php

namespace Foomo\Session;

class ImmutableProxy {
	private $obj;
	public function __construct($obj)
	{
		if(!is_object($obj)) {
			throw new \InvalidArgumentException('object expected for $obj');
		}
		$this->obj = $obj;
	}
	public function __set($name, $value)
	{
		throw new \Exception('you have to lock the session, before you write to it');
	}
	public function __get($name)
	{
		if(isset($this->obj->$name)) {
			return $this->obj->$name;
		} else {
			trigger_error('property ' . $name . ' does not exist on ' . get_class($this->obj), E_USER_NOTICE);
		}
	}
	public function __call($name, $args)
	{
		return call_user_func_array(array($this->obj, $name), $args);
	}
}