<?php

namespace Foomo\Jobs\Mock;

/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author Jan Halfar jan@bestbytes.com
 */
class DierJob extends \Foomo\Jobs\AbstractJob {

	protected $executionRule = '*   *       *       *       *';

	public function getId() {
		return sha1(__CLASS__);
	}

	public function getDescription() {
		return 'die shortly';
	}

	public function run() {

		sleep(2);
		throw new \Exception('this is bad');
	}

}
