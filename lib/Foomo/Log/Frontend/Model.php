<?php

/*
 * bestbytes-copyright-placeholder
 */

namespace Foomo\Log\Frontend;

use Foomo\Modules\Manager;
use Foomo\AutoLoader;

class Model {
	public function webTail($filters)
	{
		header('Content-Type: text/plain;charset=utf-8;');

		echo __METHOD__ . ':' . PHP_EOL . PHP_EOL . implode(PHP_EOL, $filters) . PHP_EOL . PHP_EOL;

		$filter = function (\Foomo\Log\Entry $entry) use ($filters) {
					foreach ($filters as $filter) {
						$filter = explode('::', $filter);
						if (!\call_user_func_array($filter, array($entry))) {
							return false;
						}
					}
					return true;
				};

		$utils = new \Foomo\Log\Utils();
		$utils->webTail(\Foomo\Log\Logger::getLoggerFile(), $filter);
	}
}