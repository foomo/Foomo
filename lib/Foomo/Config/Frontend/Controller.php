<?php

/*
 * bestbytes-copyright-placeholder
 */

namespace Foomo\Config\Frontend;

use Foomo\Config;
use Foomo\MVC;
use Foomo\Yaml;

/**
 * controller for the config manager
 */
class Controller {

	/**
	 * my model
	 * 
	 * @var Foomo\Config\Frontend\Model
	 */
	public $model;

	public function actionDefault() {}

	public function actionConfEditor($module = '', $domain = '', $subDomain = '')
	{
		$this->model->currentConfigModule = $module;
		$this->model->currentConfigDomain = $domain;
		$this->model->currentConfigSubDomain = $subDomain;
	}

	public function actionNewConfEditor()
	{
		
	}

	public function actionRemoveOldConfs()
	{
		Config\Utils::removeOldConfigs();
	}

	/**
	 * @param string $id
	 * 
	 * @return Config\OldConfig
	 */
	private function getOldConfById($id)
	{
		foreach (Config\Utils::getOldConfigs() as $oldConfig) {
			if ($oldConfig->id == $id) {
				return $oldConfig;
			}
		}
	}

	public function actionShowOldConf($id)
	{
		$this->model->oldConfig = $this->getOldConfById($id);
	}

	public function actionRestoreOldConf($id)
	{
		$oldConfig = $this->getOldConfById($id);
		if ($oldConfig) {
			$this->actionSetConf(\file_get_contents($oldConfig->filename), $oldConfig->module, $oldConfig->name, $oldConfig->domain);
		}
	}

	public function actionDeleteOldConf($id)
	{
		$oldConfig = $this->getOldConfById($id);
		if ($oldConfig) {
			\unlink($oldConfig->filename);
		}
		MVC::redirect('confEditor');
	}

	public function actionCreateConf($module, $domain, $subDomain = '')
	{
		Config::restoreConfDefault($module, $domain, $subDomain);
		MVC::redirect('showConf', array($module, $domain, $subDomain, 'new config created'));
	}

	public function actionShowConf($module, $domain, $subDomain, $comment)
	{
		$this->model->showConfigComment = $comment;
		$this->model->showConfigModule = $module;
		$this->model->showConfigDomain = $domain;
		$this->model->showConfigSubDomain = $subDomain;
	}

	public function actionSetConf($yaml, $module, $domain, $subDomain = '')
	{
		$conf = $this->getConfClassInst($domain);
		$conf->setValue(Yaml::parse($yaml));
		Config::setConf($conf, $module, $subDomain, $yaml);
		MVC::redirect('showConf', array($module, $domain, $subDomain));
	}

	private function getConfClassInst($domain)
	{
		$confClassName = Config::getDomainConfigClassName($domain);
		if (!class_exists($confClassName)) {
			throw new InvalidArgumentException('unknown domain configuration : ' . $domain, 1);
		}
		return new $confClassName(true);
	}

	public function actionDeleteConf($module, $domain, $subDomain)
	{
		Config::removeConf($module, $domain, $subDomain);
		MVC::redirect('default');
	}

	public function actionRestoreDefault($module, $domain, $subDomain = '')
	{
		Config::restoreConfDefault($module, $domain, $subDomain);
		MVC::redirect('showConf', array($module, $domain, $subDomain, 'config restored'));
	}

}