<?php

/*
 * This file is part of the foomo Opensource Framework.
 * 
 * The foomo Opensource Framework is free software: you can redistribute it
 * and/or modify it under the terms of the GNU Lesser General Public License as
 * published  by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 * 
 * The foomo Opensource Framework is distributed in the hope that it will
 * be useful, but WITHOUT ANY WARRANTY; without even the implied warranty
 * of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public License along with
 * the foomo Opensource Framework. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Foomo\Jobs\Frontend;
 
/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan
 */
 
class Controller
{
	/**
	 * my model
	 *
	 * @var Foomo\Jobs\Frontend\Model
	 */
	public $model;
	public function actionDefault() {}
	private function plain($output) 
	{
		\Foomo\MVC::abort();
		header('Content-Type: text/plain;charset=utf-8;');
		echo $output;
		exit;
	}
	public function actionPreviewCrontab()
	{
		$this->plain(\Foomo\Jobs\Utils::getCrontab());
	}
	public function actionInstallCrontab()
	{
		\Foomo\Jobs\Utils::installCrontab();
		$this->plain('installed cron tab');
	}
	
	public function actionStatusView($jobId) {
		$this->model->currentJobId = $jobId;
	}
}
