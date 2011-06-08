<?php

namespace World\Of\Foo\Mail;

use Foomo\Mailer;
use Foomo\Config;

/**
 * mail example
 */
class MyMail {
	/**
	 * sends a mail
	 * 
	 * @return boolean
	 */
	public function sendMyMail()
	{
		// get a mailer
		$mailer = new Mailer();

		// use the module configuration from module foo
		$mailer->setSmtpConfig(Config::getConf('foo', 'smtp'));

		// a very tiny little model
		$model = array('name' => 'Hansi');

		// get the views from module foo
		$plainView = Module::getView($this, 'plain', $model);
		$htmlView = Module::getView($this, 'html', $model);

		// send the mail
		return $booleanMailSuccess = $mailer->sendMail(
			$to = 'hansi@test.com',
			$subject = 'your test mail',
			$plaintext = $plainView->render(),
			$html = $htmlView->render()
		);
	}
}
