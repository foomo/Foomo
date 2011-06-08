<?php

namespace Foomo\MVC\View;

class Resource {
	const TYPE_JS = 'js';
	const TYPE_CSS = 'css';	
	public $type;
	public $link;
	public function __construct($type, $link)
	{
		$this->type = $type;
		$this->link = $link;
	}
	public static function js($link)
	{
		return new self(self::TYPE_JS, $link);
	}
	
	public static function css($link)
	{
		return new self(self::TYPE_CSS, $link);
	}
}