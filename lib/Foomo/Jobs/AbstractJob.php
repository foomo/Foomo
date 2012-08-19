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
	 * default max execution time
	 * 
	 * @var integer
	 */
	protected $defaultMaxExecutionTime;
	/**
	 * default memory limit
	 * 
	 * @var integer
	 */
	protected $defaultMemoryLimit;
	/**
	 * locks by default
	 * 
	 * @var boolean
	 */
	protected $defaultLock = true;
	
	/**
	 *
	 * @var string
	 */
	protected $defaultExecutionRule;
	public function __construct() 
	{
		if(empty($this->defaultMaxExecutionTime)) {
			$this->defaultMaxExecutionTime = ini_get('max_execution_time');
		}
		if(empty($this->defaultMemoryLimit)) {
			$this->defaultMemoryLimit = ini_get('memory_limit');
		}
	}
	public function getId()
	{
		return md5(serialize($this));
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
	protected function getConfig()
	{
		$myClassName = get_called_class();
		foreach(\Foomo\Config::getConfsByName(DomainConfig::NAME) as $config) {
			if($config->className == $myClassName) {
				return $config;
			}
		}
		$default = new DomainConfig(true);
		$default->className = $myClassName;
		return $default;
	}
	/**
	 * max execution time
	 * 
	 * @return integer
	 */
	public function getMaxExecutionTime()
	{
		return $this->getConfig()->maxExecutionTime;
	}
	/**
	 * ini style memory limit
	 * 
	 * @return string
	 */
	public function getMemoryLimit()
	{
		return $this->getConfig()->memoryLimit;
	}
	/**
	 * lock or not
	 * 
	 * @return boolean
	 */
	public function getLock()
	{
		return $this->getConfig()->lock;
	}
	/**
	 * 
	 * @return string
	 */
	public function getExecutionRule()
	{
		return $this->getConfig()->executionRule;
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