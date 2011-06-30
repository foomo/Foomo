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

namespace Foomo\Cache;

/**
 * get an empty resource without a fully bootstrapped system
 *
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 * @internal
 */
class EmptyResourceHack {

	public static function getEmptyResource($className, $methodName, $assocProperties, $propTypes, $expirationTime)
	{
		$resource = new \Foomo\Cache\CacheResource();
		$resource->name = $className . '::' . $methodName;
		$resource->sourceStatic = true;
		$resource->sourceClass = $className;
		$resource->sourceMethod = $methodName;
		$resource->status = \Foomo\Cache\CacheResource::STATUS_VALID;
		//get the invalidation policy from the annotations
		$resource->invalidationPolicy = \Foomo\Cache\Invalidator::POLICY_INSTANT_REBUILD;

		$resource->hits = 0;
		$argCounter = 0;
		$resource->creationTime = \time();

		$arguments = array();
		foreach ($assocProperties as $propName => $propValue) {
			$resource->properties[$propName] = $propValue;
			$arguments[] = $propValue;
		}
		$resource->id = \Foomo\Cache\Proxy::getId($resource->name, $arguments);
		$resource->propertyTypes = $propTypes;

		$resource->expirationTime = $expirationTime;
		$resource->expirationTimeFast = $expirationTime;
		return $resource;
	}

}
