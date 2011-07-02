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

/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
class ConfigYamlCommentDecorator {
	public static function getCommentedYaml(AbstractConfig $config, $yaml) {
		$refl = new \ReflectionClass($config);
		$classComment = self::extractComment($refl);
		if(!empty($classComment)) {
			$yaml = '---' . PHP_EOL . implode(PHP_EOL, $classComment) . PHP_EOL . substr($yaml, 4) ;
		}
		foreach($refl->getProperties() as $reflProp) {
			/* @var $reflProp \ReflectionProperty */
			if($reflProp->isPublic() && !$reflProp->isStatic()) {
				$comments = self::extractComment($reflProp);
				$yaml = str_replace(PHP_EOL . $reflProp->getName() . ':', PHP_EOL . (!empty($comments)?implode(PHP_EOL, $comments):'') . PHP_EOL . $reflProp->getName() . ':', $yaml);
			}
		}
		return $yaml;
	}
	private static function extractComment($refl)
	{
		$docComment = $refl->getDocComment();
		$docCommentLines = explode(PHP_EOL, $docComment);
		
		$comments = array();
		if($refl instanceof \ReflectionClass) {
			$comments[] = '# Default derived from ' . $refl->getName();
			$comments[] = '#';
		}
		foreach($docCommentLines as $docCommentLine) {
			foreach(array('/**', '*/', '@author', '@link', '@license') as $unwanted) {
				if(strpos($docCommentLine, $unwanted) !== false) {
					continue 2;
				}
			}
			$docCommentLine = str_replace('@var', 'type', $docCommentLine);
			$comments[] = '# ' . substr($docCommentLine, strpos($docCommentLine, '*') + 2);
		}
		if(!empty($comments)) {
			$commentDelimiter = '#' . str_repeat('-', 79);
			$comments = array_merge(array($commentDelimiter), $comments, array($commentDelimiter));
		}
		return $comments;
	}
}