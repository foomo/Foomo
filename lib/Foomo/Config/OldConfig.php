<?php

/*
 * bestbytes-copyright-placeholder
 */

namespace Foomo\Config;

/**
 * struct representing a deleted config
 */
class OldConfig {
	const TYPE_DELETED = 'deleted';
	const TYPE_BACKUP = 'backup';
	/**
	 * @var string
	 */
	public $id;
	/**
	 * type one of self::TYPE_...
	 * 
	 * @var string
	 */
	public $type;
	/**
	 * @var string
	 */
	public $filename;
	/**
	 * @var integer
	 */
	public $timestamp;
	/**
	 * @var string
	 */
	public $module;
	/**
	 * @var string 
	 */
	public $name;
	/**
	 * @var type 
	 */
	public $domain;
}