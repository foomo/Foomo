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

namespace Foomo\Cache\Test;

/**
 * job list that configures the test cache rebuilder job
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author bostjan bostjan.marusic@bestbytes.de
 */
class JobList  // removed so that it is hidden implements \Foomo\Jobs\JobListInterface
{

	public static function getJobs()
	{
		$className = 'Foomo\Cache\MockObjects\SampleResources';
		$object = new $className;
		$method = 'getHoroscopeData';
		$arguments = array(0, 'myLocation');
		$resource = \Foomo\Cache\Proxy::getEmptyResource($className, $method, $arguments);
		$resource->value = \call_user_func_array(array($object, $method), $arguments);

		return array(
			\Foomo\Cache\RebuildJob::create()
					->setDescription('rebuild test cache - resource')
					//->invalidateCachedProxyCall($className, 'getHoroscopeData', array(0, 'myLocation'))
					->invalidateResource($resource),
		
			\Foomo\Cache\RebuildJob::create()
					->setDescription('rebuild test cache - proxy call')
					->invalidateCachedProxyCall($className, 'getHoroscopeData', array(0, 'myLocation')),
			
			\Foomo\Cache\RebuildJob::create()
					->setDescription('rebuild test cache - query - null')
					->invalidateWithQuery($resource->name, null),
			\Foomo\Cache\RebuildJob::create()
					->setDescription('rebuild test cache - query')
					->invalidateWithQuery($resource->name, \Foomo\Cache\Persistence\Expr::idEq($resource->id))
		);
	}

}

