<?php

namespace Foomo\Config;

use Foomo\Yaml;

/**
 * - extends this to define domain specific configrations like mail, db, ...
 * - multiple inheritence is a bad idea
 * - member variables must not be objects or resources
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
		return (array) $this;
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
			foreach ($value as $k => $v) {
				$this->$k = $v;
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
		return Yaml::dump($this->getValue());
	}
}