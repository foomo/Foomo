<?php

namespace Foomo\Jobs\Mock;

/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author Jan Halfar jan@bestbytes.com
 */
class ExiterJob extends \Foomo\Jobs\AbstractJob {

	protected $executionRule = '*   *       *       *       *';

	public function getId() {
		return sha1(__CLASS__);
	}

	public function getDescription() {
		return 'exit shortly';
	}

	public function run() {
		sleep(1);
		exit;
	}

}
