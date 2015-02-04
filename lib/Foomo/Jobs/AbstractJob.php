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

	const ARG_TYPE_DAY_OF_MONTH = 'ARG_TYPE_DAY_OF_MONTH';
	const ARG_TYPE_MINUTE = 'ARG_TYPE_MINUTE';
	const ARG_TYPE_HOUR = 'ARG_TYPE_HOUR';
	const ARG_TYPE_DAY_OF_WEEK = 'ARG_TYPE_DAY_OF_WEEK';
	const ARG_TYPE_MONTH = 'ARG_TYPE_DAY_OF_WEEK';

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
	 * 
	 * @throws \InvalidArgumentException
	 */
	public function executionRule($rule)
	{
		self::validateExecutionRule($rule);
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
	 * create a job
	 * 
	 * @return \Foomo\Jobs\AbstractJob
	 */
	public static function create()
	{
		$className = get_called_class();
		return new $className;
	}

	public function getURL()
	{
		return \Foomo\Utils::getServerUrl() . \Foomo\Module::getHtdocsPath('jobRunner.php') . "/" . urlencode($this->getSecretId(Utils::getExecutionSecret()));
	}

	/**
	 * do your thing here
	 */
	abstract public function run();

	protected static function validateExecutionRule($rule)
	{
		$args = array();
		foreach (explode(' ', $rule) as $arg)
		{
			if (($arg != '' && $arg != ' ') || $arg == '*') {
				$args[] = $arg;
			}
		}
		if (count($args) != 5) {
			throw new \InvalidArgumentException('execution rule does not contain 5 parameters');
		}

		self::validateArgument($args[0], self::ARG_TYPE_MINUTE);
		self::validateArgument($args[1], self::ARG_TYPE_HOUR);
		self::validateArgument($args[2], self::ARG_TYPE_DAY_OF_MONTH);
		self::validateArgument($args[3], self::ARG_TYPE_MONTH);
		self::validateArgument($args[4], self::ARG_TYPE_DAY_OF_WEEK);

		return true;
	}

	/**
	 * 
	 * @param mixed $arg
	 * @param string $argType one of self::ART_TYPE_...
	 * @return boolean
	 * @throws @throws \IllegalArgumentException
	 */
	protected static function validateArgument($arg, $argType)
	{
		if (strpos($arg,',') !== false) {
			foreach (explode(',', $arg) as $argItem) {
				self::validateArgument($argItem, $argType);
			} 
			return true;
		}
		
		
		if ($arg == '*') {
			return true;
		}
		switch ($argType) {
			case self::ARG_TYPE_DAY_OF_MONTH:
				if ($arg < 1 || $arg > 31) {
					throw new \InvalidArgumentException('invalid day of month: ' . $arg);
				}
				break;
			case self::ARG_TYPE_MINUTE:
				if ($arg < 0 || $arg > 59) {
					throw new \InvalidArgumentException('invalid minute: ' . $arg);
				}
				break;
			case self::ARG_TYPE_HOUR:
				if ($arg < 0 || $arg > 23) {
					throw new \InvalidArgumentException('invalid hour: ' . $arg);
				}
				break;
			case self::ARG_TYPE_DAY_OF_WEEK:
				if ($arg < 0 || $arg > 6) {
					throw new \InvalidArgumentException('invalid day of week: ' . $arg);
				}
				break;
			case self::ARG_TYPE_MONTH:
				if ($arg < 1 || $arg > 12) {
					throw new \InvalidArgumentException('invalid month: ' . $arg);
				}
				break;
			default:
		}
		return true;
	}

}