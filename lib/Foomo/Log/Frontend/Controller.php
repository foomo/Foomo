<?php

/*
 * bestbytes-copyright-placeholder
 */

namespace Foomo\Log\Frontend;

use Foomo\MVC;

class Controller {

	/**
	 * @var Foomo\Log\Frontend\Model
	 */
	public $model;

	public function actionWebTail($filters)
	{
		MVC::abort();
		$rawFilters = explode(chr(10), $filters);
		$filters = array();
		foreach ($rawFilters as $rawFilter) {
			$rawFilter = trim($rawFilter);
			if (!empty($rawFilter)) {
				$filters[] = $rawFilter;
			}
		}
		$this->model->webTail($filters);
		exit;
	}

}