<?php

/*
 * bestbytes-copyright-placeholder
 */

namespace Foomo\Cache;

class CacheResourceDescription extends \Annotation {

	public $lifeTimeFast = 0;
	public $lifeTime = 0;
	public $invalidationPolicy = \Foomo\Cache\Invalidator::POLICY_INSTANT_REBUILD;
	public $dependencies = array();
	private $statusMessage = array();

	/**
	 * validates the resource and returns status ok or error message
	 *
	 * @param string $resourceName
	 *
	 * @param string[] $availableResources
	 *
	 */
	public function validate($availableResources = null)
	{
		//check lifeTime
		if (is_null($availableResources)) {
			$availableResources = \Foomo\Cache\DependencyModel::getInstance()->getAvailableResources();
		}

		if (!\is_integer($this->lifeTime)) {
			\trigger_error(__CLASS__ . __METHOD__ . "lifeTime attribute must be integer");
			$this->statusMessage[] = "lifeTime attribute must be integer";
		}

		if ($this->lifeTime < 0) {
			\trigger_error(__CLASS__ . __METHOD__ . "lifeTime attribute must be >= 0. " . $this->lifeTime . " given.");
			$this->statusMessage[] = "lifeTime attribute must be >= 0. " . $this->lifeTime . " given.";
		}

		//check lifeTimeFast
		if (!\is_integer($this->lifeTimeFast)) {
			\trigger_error(__CLASS__ . __METHOD__ . "lifeTimeFast attribute must be integer");
			$this->statusMessage[] = "lifeTimeFast attribute must be integer";
		}

		if ($this->lifeTimeFast < 0) {
			\trigger_error(__CLASS__ . __METHOD__ . "lifeTimeFast attribute must be >= 0. " . $this->lifeTimeFast . " given.");
			$this->statusMessage[] = "lifeTimeFast attribute must be >= 0. " . $this->lifeTimeFast . " given.";
		}

		//check invalidationPolicy
		if ($this->invalidationPolicy != Invalidator::POLICY_INSTANT_REBUILD
				&& $this->invalidationPolicy != Invalidator::POLICY_INVALIDATE
				&& $this->invalidationPolicy != Invalidator::POLICY_DELETE
				&& $this->invalidationPolicy != Invalidator::POLICY_DO_NOTHING) {
			\trigger_error(__CLASS__ . __METHOD__ . "invalidation policy must be one of: " . Invalidator::POLICY_INSTANT_REBUILD . ", " . Invalidator::POLICY_INVALIDATE . ", " . Invalidator::POLICY_DELETE . " => " . $this->invalidationPolicy . " found in annotation");
			$this->statusMessage[] = "invalidation policy must be one of: " . Invalidator::POLICY_INSTANT_REBUILD . ", " . Invalidator::POLICY_INVALIDATE . ", " . Invalidator::POLICY_DELETE . " => " . $this->invalidationPolicy . " found in annotation";
		}

		if ($this->invalidationPolicy == Invalidator::POLICY_DO_NOTHING) {
			\trigger_error(__CLASS__ . __METHOD__ . "Warning: Invalidation policy " . Invalidator::POLICY_DO_NOTHING . " declared. This may lead to cache inconsistency. Be sure you know what you are doing!");
			$this->statusMessage[] = "Warning: Invalidation policy " . Invalidator::POLICY_DO_NOTHING . " declared. This may lead to cache inconsistency. Be sure you know what you are doing!";
		}

		if (!is_array($this->dependencies)) {
			$deps = \explode(',', $this->dependencies);
			$this->dependencies = array();
			foreach ($deps as $dep) {
				$dep = \trim($dep);
				if (in_array($dep, $availableResources)) {
					$this->dependencies[] = $dep;
				} else {
					trigger_error($resourceName . ' has an invalid dependency to ' . $dep, \E_USER_WARNING);
					$this->statusMessage[] = $resourceName . ' has an invalid dependency to ' . $dep;
				}
			}
		}
		$this->statusMessage[] = 'OK';
	}

	public function getAnnotationValidationStatus()
	{
		if (count($this->statusMessage) > 0) {
			return $this->statusMessage[0];
		} else {
			return "Error: could not validate resource annotation";
		}
	}

}