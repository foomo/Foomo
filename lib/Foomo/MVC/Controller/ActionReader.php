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

namespace Foomo\MVC\Controller;

use Foomo\AutoLoader;
use ReflectionMethod,
	ReflectionClass;

/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
class ActionReader {
	/**
	 * read the actions on a given class
	 *
	 * @param mixed $class name or instance
	 *
	 * @return \Foomo\MVC\Controller\Action[]
	 */
	public static function read($class)
	{
		/* @var $classActions ClassActions */
        $classActions = \Foomo\Cache\Proxy::call(__CLASS__, 'cachedGetClassActions', array($class));
        if(!$classActions->isValid()) {
            // cache is invalid
            \Foomo\Cache\Manager::invalidateWithQuery(
                __CLASS__ . '::cachedGetClassActions',
                \Foomo\Cache\Persistence\Expr::propEq('class', $class),
                true,
                \Foomo\Cache\Invalidator::POLICY_DELETE
            );
            $classActions = \Foomo\Cache\Proxy::call(__CLASS__, 'cachedGetClassActions', array($class));
        }
        return $classActions->actions;
	}

    /**
     * @Foomo\Cache\CacheResourceDescription(dependencies="Foomo\AutoLoader::cachedGetClassMap")
     *
     * @param string $class
     *
     * @return ClassActions
     */
    public static function cachedGetClassActions($class)
    {
        $classActions = new ClassActions();
		$classActions->addFile($controllerClassFilename = AutoLoader::getClassFileName($class));
        $classActions->controllerDir =  dirname($controllerClassFilename);
        $reflection = new ReflectionClass($class);
        $methods = $reflection->getMethods();
        $frameActions = array();
		$actionOffset = strlen('action');
        foreach ($methods as $method) {
            /* @var $method ReflectionMethod */
            $substr = substr($method->getName(), 0, $actionOffset);
            if ($method->isPublic() && substr($method->getName(), 0, $actionOffset) == 'action') {
                $frameActions[strtolower(substr($method->getName(), $actionOffset))] = self::readMethod($method);
            }
        }
		foreach(self::searchControllerActionClasses($class) as $controllerClass) {
			$classActions->addFile(AutoLoader::getClassFileName($controllerClass));
			$classRefl = new ReflectionClass($controllerClass);
			$actionNameParts = explode('\\', $classRefl->getName());
			$actionName = substr(end($actionNameParts), $actionOffset);
			$runRefl = new ReflectionMethod($controllerClass, 'run');
			$method = self::readMethod($runRefl);
			$method->actionName = 'action' . $actionName;
			$method->actionNameShort = lcfirst($actionName);
			$frameActions[strtolower($actionName)] = $method;
		}
        $classActions->actions = $frameActions;
        return $classActions;
    }
	public static function searchControllerActionClasses($class)
	{
		$actionNamespace = substr($class, 0, strlen($class) - strpos(strrev($class), '\\') - 1) . '\\Controller';
		$classMatchComparisonBase = $actionNamespace . '\\Action';
		$expectedDepth = count(explode('\\', $actionNamespace)) + 1;
		$controllerClasses = array();
		foreach(AutoLoader::getClassMap() as $className => $filename) {
			$classParts = explode('\\', $className);
			if(count($classParts) == $expectedDepth && strpos($className, $classMatchComparisonBase) === 0) {
				$classRefl = new ReflectionClass($className);
				if($classRefl->isSubclassOf('Foomo\\MVC\\Controller\\AbstractAction') && !$classRefl->isAbstract()) {
					$controllerClasses[] = $className;
				}
			}
		}
		return $controllerClasses;
	}
	private static function readMethod(ReflectionMethod $method)
	{
		$parms = $method->getParameters();
		$parameters = array();
		foreach ($parms as $parm) {
			/* @var $parm \ReflectionParameter */
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
