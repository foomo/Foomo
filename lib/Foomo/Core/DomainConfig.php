<?php

/*
 * bestbytes-copyright-placeholder
 */

namespace Foomo\Core;

use Foomo\Config\AbstractConfig;

/**
 * core config
 *
 */
class DomainConfig extends AbstractConfig {
	const NAME = 'Foomo.core';
	/**
	 * name of all the enabled modules
	 *
	 * @var array
	 */
	public $enabledModules = array();
	/**
	 * how are foomo htdocs mapped in your web server
	 *
	 * @var string
	 */
	public $rootHttp = '/foomo';
	public function __construct($createDefault = false)
	{
		if ($createDefault) {
			$this->enabledModules = array(\Foomo\Module::NAME);
		}
	}

}