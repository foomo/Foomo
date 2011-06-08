<?php

/*
 * bestbytes-copyright-placeholder
 */

namespace Foomo\Setup;

use Foomo\Config;

/**
 * framework setup
 * @internal
 */
class Controller {
	const CONTROLLER_ID = 'setup';
	public $error;
	public $baseResourceError;

	public function actionDefault()
	{
		return $this->render();
	}

	public function actionSetupAdminUser($userName, $password, $passwordRepeat)
	{
		if (
			!empty($password) &&
			($password == $passwordRepeat) &&
			!empty($userName)
		) {
			\Foomo\BasicAuth\Utils::updateUser(\Foomo\BasicAuth::DEFAULT_AUTH_DOMAIN, $userName, $password);
		} else {
			$this->error = 'posted data are invalid';
		}
		return $this->render();
	}

	private function render()
	{
		return \Foomo\Module::getView($this, 'index', $this);
	}

}