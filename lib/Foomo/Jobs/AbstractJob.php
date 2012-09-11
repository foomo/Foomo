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
abstract class AbstractJob
{

	/**
	 * max execution time
	 * 
	 * @var integer
	 */
	protected $maxExecutionTime;

	/**
	 * memory limit
	 * 
	 * @var integer
	 */
	protected $memoryLimit;

	/**
	 * lock
	 * 
	 * @var boolean
	 */
	protected $lock = true;

	/**
	 * description
	 * @var string 
	 */
	protected $description = '';

	/**
	 * cron rule
	 * 
	 * @var string
	 */
	protected $executionRule;

	public function __construct()
	{
		if (empty($this->maxExecutionTime)) {
			$this->maxExecutionTime = ini_get('max_execution_time');
		}
		if (empty($this->memoryLimit)) {
			$this->memoryLimit = ini_get('memory_limit');
		}
	}

	/**
	 * my id - override this, if this algorythm does not work for you
	 * 
	 * @return string
	 */
	public function getId()
	{
		return sha1(serialize($this));
	}

	/**
	 * you can execute me with that
	 * 
	 * @param string $executionSecret
	 * 
	 * @return string
	 */
	public function getSecretId($executionSecret)
	{
		return sha1($this->getId() . '-' . $executionSecret);
	}

	/**
	 * override this two allow locking dependent on a jobs data
	 * 
	 * @return string
	 */
	protected function getLockId()
	{
		return __CLASS__;
	}

	/**
	 * describe what this job does
	 * 
	 * @return string
	 */
	public function getDescription()
	{
		return $this->description;
	}

	/**
	 * max execution time
	 * 
	 * @return integer
	 */
	public function getMaxExecutionTime()
	{
		return $this->maxExecutionTime;
	}

	/**
	 * ini style memory limit
	 * 
	 * @return string
	 */
	public function getMemoryLimit()
	{
		return $this->memoryLimit;
	}

	/**
	 * lock or not
	 * 
	 * @return boolean
	 */
	public function getLock()
	{
		return $this->lock;
	}

	/**
	 * 
	 * @return string
	 */
	public function getExecutionRule()
	{
		return $this->executionRule;
	}

	/**
	 * max execution time
	 * 
	 * @param integer $value php.ini style
	 * 
	 * @return \Foomo\Jobs\AbstractJob
	 */
	public function maxExecutionTime($value)
	{
		$this->maxExecutionTime = $value;
		return $this;
	}

	/**
	 * set ini style memory limit
	 * 
	 * @param string $value
	 * 
	 * @return \Foomo\Jobs\AbstractJob
	 */
	public function memoryLimit($value)
	{
		$this->memoryLimit = $value;
		return $this;
	}

	/**
	 * lock or not
	 * 
	 * @param boolean $value
	 * 
	 * @return \Foomo\Jobs\AbstractJob
	 */
	public function lock($value = true)
	{
		$this->lock = $value;
		return $this;
	}

	/**
	 * the crontab * thingie
	 * 
	 * @param string $rule see the unix crontab docs
	 * 
	 * @return \Foomo\Jobs\AbstractJob
	 */
	public function executionRule($rule)
	{
		$this->executionRule = $rule;
		return $this;
	}

	/**
	 * set description
	 * @param string $description
	 * @return \Foomo\Jobs\AbstractJob
	 */
	public function setDescription($description)
	{
		$this->description = $description;
		return $this;
	}

	/**
	 * set the crontab style execution rule for daily execution
	 * @param string $executionRule
	 * @return \Foomo\SimpleData\MongoDB\BackupJob
	 */
	public function doDaily()
	{
		$this->executionRule = '0 0 * * *';
		return $this;
	}

	/**
	 * set the crontab style execution rule for hourly execution
	 * @param string $executionRule
	 * @return \Foomo\SimpleData\MongoDB\BackupJob
	 */
	public function doHourly()
	{
		$this->executionRule = '0 * * * *';
		return $this;
	}

	/**
	 * set the crontab style execution rule for weekly execution
	 * @param string $executionRule
	 * @return \Foomo\SimpleData\MongoDB\BackupJob
	 */
	public function doWeekly()
	{
		$this->executionRule = ' 	0 0 * * 0';
		return $this;
	}

	/**
	 * set the crontab style execution rule for monthly execution
	 * @param string $executionRule
	 * @return \Foomo\SimpleData\MongoDB\BackupJob
	 */
	public function doMonthly()
	{
		$this->executionRule = '0 0 1 * *';
		return $this;
	}

	/**
	 * set the crontab style execution rule for yearly execution
	 * @param string $executionRule
	 * @return \Foomo\SimpleData\MongoDB\BackupJob
	 */
	public function doYearly()
	{
		$this->executionRule = '0 0 1 1 *';
		return $this;
	}

	/**
	 * create a job
	 * 
	 * @return \Foomo\Jobs\AbstractJob
	 */
	public static function create()
	{
		$className = get_called_class();
		return new $className;
	}

	/**
	 * do your thing here
	 */
	abstract public function run();
}