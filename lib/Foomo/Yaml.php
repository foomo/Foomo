<?php

/*
 * bestbytes-copyright-placeholder
 */

namespace Foomo;

/**
 * yaml wrapper currently using symfonys yaml implementation, which could be better ;), but thanks for it anyways guys
 *
 */
class Yaml {

	/**
	 * parse yaml
	 *
	 * @param string $yaml the yaml you want to parse
	 * @return mixed
	 */
	public static function parse($yaml)
	{
		include_once(\Foomo\ROOT . '/vendor/symfony/yaml/sfYaml.class.php');
		return \sfYaml::load($yaml);
		//include_once(\Foomo\ROOT . '/vendor/spyc-0.4.2/spyc.php');
		//return Spyc::YAMLLoad($yaml);
		//return \Symfony\Components\Yaml\Yaml::load($yaml);
	}

	/**
	 * dump sth as yaml
	 *
	 * @param mixed $var
	 * @return string
	 */
	public static function dump($var)
	{
		//include_once(\Foomo\ROOT . '/vendor/spyc-0.4.2/spyc.php');
		//return Spyc::YAMLDump($var,2);
		include_once(\Foomo\ROOT . '/vendor/symfony/yaml/sfYaml.class.php');
		return \sfYaml::dump($var);
		//return \Symfony\Components\Yaml\Yaml::dump($var);
	}

}