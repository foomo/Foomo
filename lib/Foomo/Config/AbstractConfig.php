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

namespace Foomo\Config;

use Foomo\Yaml;

/**
 * - extends this to define domain specific configrations like mail, db, ...
 * - multiple inheritence is a bad idea
 * - member variables must not be objects or resources
 *
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
abstract class AbstractConfig
{
	//---------------------------------------------------------------------------------------------
	// ~ Public methods
	//---------------------------------------------------------------------------------------------

	/**
	 * derive the name from the class
	 *
	 * @internal
	 * @return string
	 */
	public function getName()
	{
		$calledClass = get_called_class();
		$classConstantName = $calledClass . '::NAME';
		if (!defined($classConstantName)) throw new \Exception($calledClass . ' does not a a NAME constant defined!');
		return constant($classConstantName);
	}

	/**
	 * get the configuration array
	 *
	 * @internal
	 * @return array
	 */
	public function getValue()
	{
		$ret = array();
		$refl = new \ReflectionClass($this);
		/* @var $reflProperty \ReflectionProperty */
		foreach($refl->getProperties() as $reflProperty) {
			if($reflProperty->isPublic()) {
				$propName = $reflProperty->getName();
				$val = $this->$propName;
				if(is_object($val)) {
					$val = (array) $val;
				}
				$ret[$propName] = $val;
			}
		}
		return $ret;
	}

	/**
	 * set the configuration array
	 *
	 * @internal
	 * @param array $value
	 */
	public function setValue($value)
	{
		if (is_array($value)) {
			$refl = new \ReflectionClass($this);
			foreach ($value as $k => $v) {
				/* @var $reflProp \ReflectionProperty */
				if($refl->hasProperty($k)) {
					$reflProp = $refl->getProperty($k);
					if($reflProp->isPublic() || $reflProp->isProtected()) {
						$this->$k = $v;
					}
				}
			}
		}
	}

	/**
	 * will be called to get a default if no config is present
	 *
	 * @internal
	 * @return Foomo\Config\AbstractConfig
	 */
	public function getDefault()
	{
		return \Foomo\Config::getDefaultConfig(\constant(\get_called_class() . '::NAME'))->getValue();
	}

	/**
	 * hook in if you need to do stuff onSave
	 */
	public function saved()
	{
	}

	//---------------------------------------------------------------------------------------------
	// ~ Magic methods
	//---------------------------------------------------------------------------------------------

	/**
	 * @return string
	 */
	public function __toString()
	{
		return ConfigYamlCommentDecorator::getCommentedYaml(
			$this, 
			'---' . PHP_EOL . Yaml::dump($this->getValue())
		);
	}
}