<?php

/*
 * This file is part of the foomo Opensource Framework.
 *
 * The foomo Opensource Framework is free software: you can redistribute it
 * and/or modify it under the terms of the GNU Lesser General Public License as
 * published  by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * The foomo Opensource Framework is distributed in the hope that it will
 * be useful, but WITHOUT ANY WARRANTY; without even the implied warranty
 * of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License along with
 * the foomo Opensource Framework. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Foomo\Log\Frontend;

use Foomo\Modules\Manager;
use Foomo\AutoLoader;

/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
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