<?php

/*
 * bestbytes-copyright-placeholder
 */

namespace Foomo\Config;
/**
 * our own little exception
 */
class Exception extends \Exception {
	const CODE_RUN_MODE_NOT_SET = 1;
	const MESSAGE_RUN_MODE_NOT_SET = 'run mode is not set';
}