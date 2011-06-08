<?php

/*
 * bestbytes-copyright-placeholder
 */

namespace Foomo\Config\Frontend;

/**
 * model for the config manager
 */
class Model {

	public $showConfigModule;
	public $showConfigDomain;
	public $showConfigSubDomain;
	public $showConfigComment;
	public $currentConfigModule;
	public $currentConfigDomain;
	public $currentConfigSubDomain;
	public $currentConfigComment;
	/**
	 * @var Foomo\Config\OldConfig
	 */
	public $oldConfig;
}