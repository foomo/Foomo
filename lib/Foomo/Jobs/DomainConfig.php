<?php

/*
 * This file is part of the foomo Opensource Framework.
 * 
 * The foomo Opensource Framework is free software: you can redistribute it
 * and/or modify it under the terms of the GNU Lesser General Public License as
 * published  by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 * 
 * The foomo Opensource Framework is distributed in the hope that it will
 * be useful, but WITHOUT ANY WARRANTY; without even the implied warranty
 * of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public License along with
 * the foomo Opensource Framework. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Foomo\Jobs;
 
/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author Jan Halfar jan@bestbytes.com
 */
class DomainConfig extends \Foomo\Config\AbstractConfig
{
	const NAME = 'Foomo.Jobs.jobConfig';
	/**
	 * php.ini style memory_limit setting
	 * 
	 * @var string
	 */
	public $memoryLimit;
	/**
	 * php.ini max_execution_time  in seconds 0 is no limit
	 * 
	 * @var integer
	 */
	public $maxExecutionTime;
	/**
	 * the job will try to get a lock
	 * 
	 * @var boolean
	 */
	public $lock = true;
	/**
	 * class name of the job
	 * 
	 * @var string
	 */
	public $className;
	/**
	 * will be passed to cron
	 * 
	 * @var string
	 */
	public $executionRule;
    //---------------------------------------------------------------------------------------------
    // ~ Constructor
    //---------------------------------------------------------------------------------------------
    public function __construct($createDefault = false)
    {
		if($createDefault) {
			$this->memoryLimit = ini_get('memory_limit');
			$this->maxExecutionTime = ini_get('max_execution_time');
		}
    }
}