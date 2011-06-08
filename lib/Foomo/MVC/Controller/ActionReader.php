<?php

/*
 * bestbytes-copyright-placeholder
 */

namespace Foomo\MVC\Controller;

use ReflectionMethod,
	ReflectionClass;

class ActionReader {

	/**
	 * read the actions on a given class
	 *
	 * @param mixed $class name or instance
	 *
	 * @return Foomo\MVC\Controller\Action[]
	 */
	public static function read($class)
	{
		$reflection = new ReflectionClass($class);
		$methods = $reflection->getMethods();
		$frameActions = array();
		foreach ($methods as $method) {
			/* @var $method ReflectionMethod */
			$substr = substr($method->getName(), 0, strlen('action'));
			if ($method->isPublic() && substr($method->getName(), 0, strlen('action')) == 'action') {
				$frameActions[strtolower(substr($method->getName(), strlen('action')))] = self::readMethod($method);
			}
		}
		return $frameActions;
	}

	private static function readMethod(ReflectionMethod $method)
	{
		$parms = $method->getParameters();
		$parameters = array();
		foreach ($parms as $parm) {
			/* @var $parm ReflectionParameter */
			$newParm = self::extractParameter($parm->getName(), $method->getDocComment());
			//die('a');
			if (is_object($parm->getClass())) {
				$newParm->type = $parm->getClass()->getName();
			}
			if ($parm->getClass()) {
				$newParm->type = $parm->getClass()->getName();
			}
			if ($parm->isOptional()) {
				$newParm->optional = true;
			}
			$parameters[$parm->getName()] = $newParm;
		}
		$controllerAction = new Action($method->getDeclaringClass()->getName(), $method->getName(), $parameters);
		return $controllerAction;
	}

	/**
	 * extract parameter from method doc
	 *
	 * @param string $parameterName
	 * @param string $docComment
	 * 
	 * @return ActionParameter
	 */
	private static function extractParameter($parameterName, $docComment)
	{
		$lines = explode(PHP_EOL, $docComment);
		$ret = new ActionParameter();
		$ret->name = $parameterName;
		foreach ($lines as $line) {
			$line = trim(str_replace('*', '', $line));
			if (strpos($line, '@') === 0) {
				$lineParts = explode(' ', $line);
				$cleanParts = array();
				foreach ($lineParts as $linePart) {
					$linePart = trim($linePart);
					if (strlen($linePart) > 0) {
						array_push($cleanParts, $linePart);
					}
				}
				$lineParts = $cleanParts;
				switch ($lineParts[0]) {
					case'@param':
						if (count($lineParts) == 3 && $lineParts[2] == '$' . $parameterName) {
							$ret->type = $lineParts[1];
						}
						break;
				}
			}
		}
		return $ret;
	}

}