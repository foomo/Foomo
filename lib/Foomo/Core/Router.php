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

namespace Foomo\Core;

use Foomo\AutoLoader;
use Foomo\Modules\MakeResult;
use Foomo\Modules\Manager;
use Foomo\Router as R;

/**
 * frontend for foomo
 * 
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
class Router extends R {
	private function out($msg, $indent = 0)
	{
		echo str_repeat('	', $indent) . $msg . PHP_EOL;
	}
	public function make($targets)
	{
		$targets = explode(',', $targets);
		if(empty($targets)) {
			die('no targets given');
		}
		$i = 0;
		$cleanTargets = array();
		foreach($targets as $target) {
			$cleanTargets[] = trim($target);
		}
		$targetResults = Manager::make($targets);
		foreach($targetResults as $target => $results) {
			if($i > 0) {
				$this->out('-----------------------------------------------------');
			}
			$this->out('making ' . $target);
			$this->out('-----------------------------------------------------');

			foreach($results as $result) {
				/* @var $result MakeResult */
				$this->out($result->moduleName . ($result->wasSuccessful()?' SUCCESS: ':' FAILURE'), 0);
				foreach($result->entries as $entry) {
					$this->out(
						$entry->level . ': ' . $entry->message,
						1
					);
				}
			}
			$i ++;
		}
	}
	public function help()
	{
		$this->out('this is foomos core rest interface - usage:');
		$this->out('/help - this page', 1);
		$this->out('/resetAutoLoader - reset the class auto loader', 1);
		$this->out('/make/:targets - make something like /make/clean,all', 1);
		$this->out('/listModules - list modules', 1);
		$this->out('/enableModule/:module - enable a module (and all its dependencies)', 1);
		$this->out('/disableModule/:module - disable a module', 1);
		$this->out('/disableAllModules - disable all modules (except Foomo)', 1);
	}
	public function resetAutoLoader()
	{
		$this->out('resetting the auto loader');
		$this->out(\strip_tags(AutoLoader::resetCache()));
	}
	public function enableModule($name)
	{
		if(!$this->isJSONRequest()) {
			$this->out('enabling module ' . $name);
		}
		Manager::enableModule($name, true);
		$this->listModules();
	}
	public function disableModule($name)
	{
		$this->out('disabling module ' . $name);
		Manager::disableModule($name);
		$this->listModules();
	}
	public function disableAllModules()
	{
		Manager::disableAllModules();
		$this->listModules();
	}
	public function listModules()
	{
		if($this->isJSONRequest()) {
			$this->listModulesMachine();
		} else {
			$this->listModulesHuman();
		}
	}
	private function replyToMachine($data)
	{
		header('Content-Type: application/json');
		if(defined('JSON_PRETTY_PRINT')) {
			echo json_encode($data, JSON_PRETTY_PRINT);
		} else {
			echo json_encode($data);
		}
	}
	private function isJSONRequest()
	{
		$headers = getallheaders();
		return isset($headers['Accept']) && strpos($headers['Accept'], 'application/json') !== false;
	}
	private function listModulesHuman()
	{
		$this->out('enabled modules:');
		$enabledModules = Manager::getEnabledModules();
		foreach($enabledModules as $enabledModule) {
			$this->out($enabledModule, 1);
		}
		$this->out('available modules:');
		foreach(Manager::getAvailableModules() as $availableModule) {
			if(!in_array($availableModule, $enabledModules)) {
				$this->out($availableModule, 1);
			}
		}
	}
	private function listModulesMachine()
	{
		$this->replyToMachine(array(
			'enabledModules' => Manager::getEnabledModules(),
			'availableModules' => Manager::getAvailableModules()
		));
	}
	private static function getBaseURL()
	{
		return \Foomo\ROOT_HTTP . '/core.php';
	}
	public static function run()
	{
		header('Content-Type: text/plain;charset=utf-8;');
		$coreRouter = new Router;
		$coreRouter->addRoutes(array(
			'/make/:targets' => 'make',
			'/help' => 'help',
			'/enableModule/:name' => 'enableModule',
			'/disableModule/:name' => 'disableModule',
			'/disableAllModules' => 'disableAllModules',
			'/listModules' => 'listModules',
			'/resetAutoLoader' => 'resetAutoLoader',
			'*' => 'help'
		));
		$coreRouter->execute();
	}
}
