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