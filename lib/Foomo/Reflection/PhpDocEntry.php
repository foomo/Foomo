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

namespace Foomo\Reflection;

/**
 * php doc entry converted to an object
 * 
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 * @todo make more generic and support annotations
 */
class PhpDocEntry {
	/**
	 * @var string
	 */
	public $namespace;
	/**
	 * @var \Foomo\Reflection\PhpDocArg
	 */
	public $var;
	/**
	 * @var PhpDocProperty[]
	 */
	public $properties = array();
	/**
	 * @var \Foomo\Reflection\PhpDocArg[]
	 */
	public $parameters = array();
	/**
	 * @var \Foomo\Reflection\PhpDocArg
	 */
	public $return;
	/**
	 * @var \Foomo\Reflection\PhpDocArg[]
	 */
	public $throws = array();
	/**
	 * @var \Foomo\Reflection\PhpDocArg[]
	 */
	public $serviceMessage = array();
	/**
	 * hints for wsdl generation
	 *
	 * @var string
	 */
	public $wsdlGen;
	/**
	 * hints for service generation
	 *
	 * @var string
	 */
	public $serviceGen;
	/**
	 * some prosa
	 *
	 * @var string
	 */
	public $desc = '';
	/**
	 * again prosa or a poem if you like
	 *
	 * @var string
	 */
	public $comment = '';
	/**
	 * @var string
	 */
	public $author;
	/**
	 * @var string
	 */
	public $see;

	public function __construct($docString = null, $namespace = '')
	{
		$this->namespace = $namespace;
		if (!is_null($docString)) {
			// do not use a PHP_EOL delimiter here, because it will break your system on windows with unix sources
			$lines = explode(chr(10), str_replace(array(chr(9), chr(13)), array(' ', ''), $docString));
			foreach ($lines as $line) {
				//trigger_error($docString);
				$line = trim(str_replace(array('/*', '*/', '*'), array('', '', ''), $line));
				if (strpos($line, '@') !== false) {
					$cleanLineParts = $this->cleanLine($line);
					switch ($cleanLineParts[0]) {
						case'@property':
						case'@property-read':
						case'@property-write':
							if (isset($cleanLineParts[2])) {
								$read = $write = true;
								if ($cleanLineParts[0] == '@property-write') {
									$read = false;
								} elseif ($cleanLineParts[0] == '@property-read') {
									$write = false;
								}
								$name = $cleanLineParts[2];
								$type = $this->resolveType($cleanLineParts[1]);
								$comment = $this->readLineComment($line);
								array_push($this->properties, new PhpDocProperty(str_replace('$', '', $name), $type, $comment, $read, $write));
							}
							break;
						case'@var':
							// inline comments
							if (count($cleanLineParts) == 3) {
								// inline doc comment
								$varName = substr($cleanLineParts[1], 1);
								$this->var = new PhpDocArg($varName, $this->resolveType($cleanLineParts[2]), 'see class docs ' . $this->resolveType($cleanLineParts[2]));
							} else {
								$myPropName = substr($cleanLineParts[0], 1);
								$this->$myPropName = new PhpDocArg('', $this->resolveType($cleanLineParts[1]), $this->readLineComment($line));
							}
							break;
						case'@throws':
						case'@return':
						case'@serviceMessage':
							if (isset($cleanLineParts[1])) {
								$myPropName = substr($cleanLineParts[0], 1);
								$docArg = new PhpDocArg('', $this->resolveType($cleanLineParts[1]), $this->readLineComment($line));
								if (is_array($this->$myPropName)) {
									array_push($this->$myPropName, $docArg);
								} else {
									$this->$myPropName = $docArg;
								}
							}
							break;
						case'@param':
							if (isset($cleanLineParts[2])) {
								array_push($this->parameters, new PhpDocArg(substr($cleanLineParts[2], 1), $this->resolveType($cleanLineParts[1]), $this->readLineComment($line)));
							}
							break;
						case'@desc':
							var_dump($cleanLineParts);
							die();
							break;
						case '@author':
						case '@see':
							$myPropName = substr($cleanLineParts[0], 1);
							$this->$myPropName = implode(' ', array_slice($cleanLineParts, 1));
							break;
						case'@asClass':
						case'@asclass':
							if (isset($cleanLineParts[1])) {
								$this->asClass = $cleanLineParts[1];
							}
							break;
						case'@wsdlGen':
						case'@wsdlgen':
						case'@serviceGen':
						case'@servicegen':
							if (isset($cleanLineParts[1])) {
								$this->wsdlGen = $this->serviceGen = $cleanLineParts[1];
							}
							break;
						default:
						// trigger_error('unknown doc type '. $cleanLineParts[0]);
					}
				} else {
					if (!(strpos($line, '/') === 0) && $line != '') {
						$this->comment .= $line . PHP_EOL;
					}
				}
			}
			$this->comment = trim(trim($this->comment, PHP_EOL));
		}
	}
	/**
	 * @internal
	 * @param string $type
	 * @return string
	 */
	public function resolveType($type)
	{
		$type = trim($type);
		$typeIsArray = substr($type, -2) == '[]';
		if(in_array( ($typeIsArray?substr($type,0,-2):$type), array('bool', 'boolean', 'string', 'int', 'integer', 'float', 'double', 'resource', 'mixed', 'array', 'hash'))) {
			return $type;
		} else {
			$type = $typeIsArray?substr($type, 0, -2):$type;
			if(substr($type,0,1) == '\\') {
				$absolute = true;
				$type = substr($type, 1);
			} else {
				$absolute = false;
			}
			if(!$absolute && class_exists($this->namespace . '\\' . $type)) {
				$type = (!empty($this->namespace)?$this->namespace . '\\':'') . $type;
			}
			return $typeIsArray?$type.'[]':$type;
		}
	}
	
	private function cleanLine($line)
	{
		$lineParts = explode(' ', $line);
		$cleanLineParts = array();
		foreach ($lineParts as $linePart) {
			if ($linePart != ' ') {
				array_push($cleanLineParts, $linePart);
			}
		}
		return $cleanLineParts;
	}

	private function readLineComment($line)
	{
		$cleanLineParts = $this->cleanLine($line);

		$comment = '';
		switch ($cleanLineParts[0]) {
			case '@param':
				if (count($cleanLineParts) > 3) {
					$comment = implode(' ', array_slice($cleanLineParts, 3));
				}
				break;
			default:
				if (count($cleanLineParts) > 2) {
					$comment = implode(' ', array_slice($cleanLineParts, 2));
				}
		}
		return $comment;
	}

}
