<?php
/**
 * a translation usage sample service
 */
class SampleService extends AnotherService {
	/**
	 * my translation
	 *
	 * @var RadTranslation
	 */
	protected $translation;
	/**
	 * a login
	 * 
	 * @param string $name name
	 * @param string $password password
	 * @param string[] $localeChain your favourite locales
	 *
	 * @return LoginSuccess
	 */
	public function login($name, $password, $localeChain)
	{
		$this->translation = RadModuleMyModule::getTranslation('services', array('en'));
		if($this->checkCredentials($name, $password)) {
			$message = $this->translation->_('LOGIN_WELCOME') . ' ' . $name;
		} else {
			$message = $this->translation->_('LOGIN_NAME_OR_PASSWORD_WRONG') . ' ' . $name;
		}
		$loginSuccess = new LoginSuccess;
		$loginSuccess->success = $success;
		$loginSuccess->message = $message;
		return $loginSuccess;
	}

}
