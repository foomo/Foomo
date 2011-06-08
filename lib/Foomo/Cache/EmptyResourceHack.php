<?php

/*
 * bestbytes-copyright-placeholder
 */

namespace Foomo\Cache;

/**
 * get an empty resource without a fully bootstrapped system
 * 
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
