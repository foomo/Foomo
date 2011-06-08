<?php
/*
 * bestbytes-copyright-placeholder
 */

namespace Foomo\BasicAuth\Frontend;

use Foomo\BasicAuth\Utils;
use Foomo\MVC;

class Controller {
	/**
	 * @var Foomo\BasicAuth\Frontend\Model
	 */
	public $model;
	public function actionDefault() {}
	public function actionUpdateUser($domain, $user, $password)
	{
		Utils::updateUser($domain, $user, $password);
		MVC::redirect('showDomain', array($domain));
	}
	public function actionDeleteDomain($domain)
	{
		Utils::deleteDomain($domain);
	}
	public function actionCreateDomain($domain)
	{
		Utils::createDomain($domain);
	}
	public function actionDeleteUser($domain, $user)
	{
		Utils::deleteUser($domain, $user);
		MVC::redirect('showDomain', array($domain));
	}
	public function actionShowDomain($domain)
	{
		$this->model->currentAuthDomain = $domain;
	}
}