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
 * ha we added yaml extension support
 *
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
class Yaml {
	/**
	 * parse yaml
	 *
	 * @param string $yaml
	 *
	 * @return array
	 *
	 * @throws \Exception
	 */
	public static function parse($yaml)
	{
		if(function_exists('yaml_parse')) {
			$data = yaml_parse($yaml);
			if(!is_array($data)) {
				$error = error_get_last();
				throw new \Exception($error['message'], 1);
			} else {
				return $data;
			}
		} else {
			include_once(\Foomo\ROOT . '/vendor/symfony/yaml/sfYaml.class.php');
			return \sfYaml::load($yaml);
		}
	}

	/**
	 * dump sth as yaml
	 *
	 * @param mixed $var
	 *
	 * @return string
	 */
	public static function dump($var)
	{
		if(function_exists('yaml_emit')) {
			return yaml_emit($var);
		} else {
			include_once(\Foomo\ROOT . '/vendor/symfony/yaml/sfYaml.class.php');
			return \sfYaml::dump($var);
		}
	}

}