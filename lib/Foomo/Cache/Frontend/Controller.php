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

namespace Foomo\Cache\Frontend;

use Foomo\Cache\Persistence\Expr;

/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
class Controller {

	/**
	 * @var Model
	 */
	public $model;

	public function actionDefault()
	{
		$this->model->currentInvalidationList = array();
	}

	public static function actionPopulateFastCache()
	{
		\Foomo\MVC::abort();
		header('Content-Type: text/plain;charset=utf-8;');
		\Foomo\cache\Manager::populateFastCache();
		echo 'Done. ' . PHP_EOL;
		exit;
	}

	public function actionRefreshDependencyModel($resourceName)
	{
		$this->model->currentResourceName = $resourceName;
		\Foomo\Cache\DependencyModel::getInstance()->getDirectory(true);
		\Foomo\MVC::redirect('showCachedItems',array($resourceName));
	}

	public function actionRefreshDependencyModelAll()
	{
		\Foomo\Cache\DependencyModel::getInstance()->getDirectory(true);
	}

	public static function actionValidateStorageStructure($resourceName)
	{
		\Foomo\MVC::abort();
		header('Content-Type: text/plain;charset=utf-8;');
		\Foomo\cache\Manager::validateStorageStructure($resourceName);
		echo 'Done. ' . PHP_EOL;
		exit;
	}

	public static function actionSetUpCacheStructure()
	{
		\Foomo\MVC::abort();
		header('Content-Type: text/plain');
		$fastPersistor = \Foomo\Cache\Manager::getFastPersistor();
		if($fastPersistor) {
			$fastPersistor->reset();
		}
		\Foomo\Cache\Manager::getQueryablePersistor()->reset(null, true);
		echo 'done';
		exit;
	}

	public function actionCheckDialog($operation)
	{
		if (is_array($operation)) {
			$this->model->currentOperation = $operation[0];
			$this->model->currentResourceName = $operation[1];
		} else {
			$this->model->currentOperation = $operation;
		}
	}

	public function actionShowResourceTree($resourceName)
	{
		$this->model->currentResourceName = $resourceName;
	}

	public function actionShowCachedItems($resourceName)
	{

		$this->model->currentResourceName = $resourceName;
	}

	public function actionShowResource($resourceName, $resourceId)
	{
		$this->model->currentResourceName = $resourceName;
		$this->model->currentResourceId = $resourceId;
		$this->model->setCurrentResourceForId($resourceName, $resourceId);
	}

	public function actionRebuildId($resourceName, $resourceId, $invalidationPolicy = \Foomo\Cache\Invalidator::POLICY_INSTANT_REBUILD)
	{
		\Foomo\MVC::abort();
		header('Content-Type: text/plain');
		echo 'Ivalidate called for resource with name ' . $resourceName . ' with invalidation policy ' . $invalidationPolicy . \PHP_EOL;
		echo '---------------------------------------------------------------------------------------------------------------' . \PHP_EOL;
		echo '--- as a result, the following cache resources will be invalidated:' . \PHP_EOL;

		$this->model->currentResourceName = $resourceName;
		$this->model->currentResourceId = $resourceId;
		$expr = \Foomo\Cache\Persistence\Expr::groupAnd(\Foomo\Cache\Persistence\Expr::idEq($resourceId));
		$iter = \Foomo\Cache\Manager::query($this->model->currentResourceName, $expr);

		if (count($iter) == 1) {
			$resource = $iter->current();
			$resource->invalidationPolicy = $invalidationPolicy;
		} else {
			$this->model->currentInvalidationList = null;
			return;
		}

		$invalidator = new \Foomo\Cache\Invalidator;

		$this->model->currentInvalidationList = $invalidator->getInInvalidationList($resource, true);
		$invalidator->invalidate($resource, true, true);
		exit;
	}

	public function actionAdvancedInvalidation($resourceName, $invalidationPolicy = \Foomo\Cache\Invalidator::POLICY_INSTANT_REBUILD, $expressionString = '', $expressionVerified = false)
	{
		if (empty($invalidationPolicy)) {
			$invalidationPolicy = \Foomo\Cache\Invalidator::POLICY_INSTANT_REBUILD;
		}
		$expressionVerified = ($expressionVerified == 'true') ? true : false;
		$this->model->currentResourceName = $resourceName;
		$this->model->validateUserExpression($expressionString);
		$this->model->currentInvalidationPolicy = $invalidationPolicy;

		//if expression was verified then do the actual invalidate

		if ($expressionVerified && !is_null($this->model->advancedInvalidationUserExpression)) { // check the expression again
			\Foomo\MVC::abort();
			header('Content-Type: text/plain');
			$this->model->invalidateResources($resourceName, $this->model->advancedInvalidationUserExpression, $invalidationPolicy);
			exit;
		}

		// move to model ...
		// prefetch query results;
		$this->model->currentInvalidationList = array();
		if (!\is_null($this->model->advancedInvalidationUserExpression)) {
			$this->model->prefectchResourcesToInvalidate($resourceName, $this->model->advancedInvalidationUserExpression);
		}
	}

	public function actionRebuildResourcesWithName($resourceName, $invalidationPolicy)
	{
		\Foomo\MVC::abort();
		header('Content-Type: text/plain');
		echo 'Invalidation called for resource with name ' . $resourceName . ' and policy:' . $invalidationPolicy . \PHP_EOL;
		echo '---------------------------------------------------------------------------------------------------------------' . \PHP_EOL;
		echo 'the following cache resources will be invalidated:' . \PHP_EOL;
		$this->model->currentResourceName = $resourceName;
		$this->model->currentResourceId = null;
		$this->model->currentInvalidationList = array();
		//get all cached ids first
		$expr = \Foomo\Cache\Persistence\Expr::groupAnd(\Foomo\Cache\Persistence\Expr::idNe('This can never be an id'));
		$iter = \Foomo\Cache\Manager::query($resourceName, $expr);

		$invalidator = new \Foomo\Cache\Invalidator;
		foreach ($iter as $resource) {
			$resource->invalidationPolicy = $invalidationPolicy;
			$invalidationList = $invalidator->getInInvalidationList($resource, true);
			$this->model->currentInvalidationList = \array_merge($this->model->currentInvalidationList, $invalidationList);
			$invalidator->invalidate($resource, true, true);
		}

		exit;
	}

	public function actionPreviewRebuildResourcesWithName($resourceName)
	{
		\Foomo\MVC::abort();
		header('Content-Type: text/plain');
		echo 'Preview of invalidation tree for resource with name ' . $resourceName . \PHP_EOL;
		echo '---------------------------------------------------------------------------------------------------------------' . \PHP_EOL;
		echo 'the following cache resources would be invalidated:' . \PHP_EOL;
		$this->model->currentResourceName = $resourceName;
		$this->model->currentResourceId = null;
		$this->model->currentInvalidationList = array();
		//get all cached ids first
		$expr = \Foomo\Cache\Persistence\Expr::groupAnd(\Foomo\Cache\Persistence\Expr::idNe('This can never be an id'));
		$iter = \Foomo\Cache\Manager::query($resourceName, $expr);

		$invalidator = new \Foomo\Cache\Invalidator;
		foreach ($iter as $resource) {
			$invalidationList = $invalidator->getInInvalidationList($resource, true);
			$this->model->currentInvalidationList = \array_merge($this->model->currentInvalidationList, $invalidationList);
		}

		foreach ($this->model->currentInvalidationList as $resource) {
			echo '--->' . $resource->name . 'with id: ' . $resource->id . \PHP_EOL;
		}
		exit;
	}

	public function actionPreviewRebuildId($resourceName, $resourceId)
	{
		\Foomo\MVC::abort();
		header('Content-Type: text/plain');
		echo 'Preview of invalidation for resource with name ' . $resourceName . ' and id ' . $resourceId . \PHP_EOL;
		echo '---------------------------------------------------------------------------------------------------------------' . \PHP_EOL;
		echo '--- the following cache resources would be invalidated:' . \PHP_EOL;

		$this->model->currentResourceName = $resourceName;
		$this->model->currentResourceId = $resourceId;
		$expr = \Foomo\Cache\Persistence\Expr::groupAnd(\Foomo\Cache\Persistence\Expr::idEq($resourceId));
		$iter = \Foomo\Cache\Manager::query($this->model->currentResourceName, $expr);

		if (count($iter) == 1) {
			$resource = $iter->current();
			$resource->invalidationPolicy = $invalidationPolicy;
		} else {
			$this->model->currentInvalidationList = null;
			return;
		}

		$invalidator = new \Foomo\Cache\Invalidator;

		$this->model->currentInvalidationList = $invalidator->getInInvalidationList($resource, true);
		foreach ($this->model->currentInvalidationList as $resource) {
			echo '--->' . $resource->name . 'with id: ' . $resource->id . \PHP_EOL;
		}
		exit;
	}

	public function actionSetUpOne($resourceName, $sure = 'false')
	{
		$this->model->currentResourceName = $resourceName;
		if ($sure == true) {
			\Foomo\MVC::abort();
			header('Content-Type: text/plain');
			\Foomo\Cache\Manager::getFastPersistor()->reset();
			\Foomo\Cache\Manager::getQueryablePersistor()->reset($resourceName, true, true);
			echo 'cache setup complete';
			exit;
		}
	}
}