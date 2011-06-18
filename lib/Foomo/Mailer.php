<?php

/*
 * bestbytes-copyright-placeholder
 */

namespace Foomo;

/**
 * a simple mail interface
 * @todo implement method chaining pattern ->to()->addHeaders()->addAttachments()->send()
 */
class Mailer {

	/**
	 * settings to connect to a smtp server
	 *
	 * @var Foomo\Config\Smtp
	 */
	protected $config;
	public static $lastSubject;
	public static $lastHeaders;
	public static $lastTo;
	public static $lastPlain;
	public static $lastHtml;
	public static $lastSuccess;
	public static $logLast = false;
	/**
	 * if you do not want to have mails going out set it to false
	 *
	 * @var boolean
	 */
	public static $enabled = true;
	public $attachments = array();
	public $htmlImages = array();
	private $mailer;
	/**
	 * contains the last pear error
	 *
	 * @var string
	 */
	private $lastError = '';
	public function setSmtpConfig(Config\Smtp $config)
	{
		$this->config = $config;
	}

	public function addAttachment(Mailer\Attachment $attachment)
	{
		$this->attachments[] = $attachment;
	}

	public function addHTMLImage(Mailer\HTMLImage $image)
	{
		$this->htmlImages[] = $image;
	}

	private function checkMailer()
	{
		if (!isset($this->mailer)) {
			/* @var $smtpConfig Foomo\Config\Smtp */
			$smtpConfig = null;
			if ($this->config instanceof \Foomo\Config\Smtp) {
				$smtpConfig = $this->config;
			} else {
				$smtpConfig = Config::getConf(\Foomo\Module::NAME, Config\Smtp::NAME);
			}
			$this->mailer = $smtpConfig ? \Mail::factory('smtp', $smtpConfig->toPearMailerFactoryArray()) : \Mail::factory('mail');
		}
	}

	/**
	 * send a mail - if you want to set the sender use $headers = array('From' => 'sender@sender.com');
	 *
	 * @param string $to
	 * @param string $subject
	 * @param string $plaintext
	 * @param string $html
	 * @param array $headers
	 * @param Mailer\Attachment[] $attachments will only work with html and plaintext
	 * @param Mailer\HTMLImage[] $htmlImages will only work with html and plaintext
	 *
	 * @return boolean
	 */
	public function sendMail($to, $subject, $plaintext = '', $html = '', $headers = array(), $attachments = array(), $htmlImages = array())
	{

		include_once('Mail.php');
		include_once('Mail/mime.php');

		$this->checkMailer();

		$hdrs = array(
			'Subject' => $subject,
			'To' => $to,
				// 'Content-type' => 'text/plain; charset=utf-8'
		);
		if (!isset($headers['Return-Path']) && !empty($headers['From'])) {
			$hdrs['Return-Path'] = $headers['From'];
		}
		foreach ($headers as $headerName => $headerValue) {
			$hdrs[$headerName] = $headerValue;
		}
		if (!empty($plaintext) && !empty($html)) {
			// multipart
			$mime = new \Mail_mime(PHP_EOL);
			if (!empty($plaintext)) {
				$mime->setTXTBody($plaintext);
			}
			if (!empty($html)) {
				$mime->setHTMLBody($html);
			}
			$attachments = array_merge($attachments, $this->attachments);
			foreach ($attachments as $attachment) {
				/* @var $attachment Foomo\Mailer\Attachment */
				$mime->addAttachment(
						$attachment->fileName, $attachment->mimeType, $attachment->name, true, $attEncoding = 'base64', $attDisposition = $attachment->disposition, $attCharset = $attachment->charset, $attLanguage = $attachment->language, $attLocation = $attachment->location, $att_n_encoding = null, $att_f_encoding = null, $attDescription = $attachment->description
				);
			}
			$htmlImages = array_merge($htmlImages, $this->htmlImages);
			foreach ($htmlImages as $htmlImage) {
				/* @var $htmlImage Foomo\Mailer\HTMLImage */
				$mime->addHTMLImage(
						$htmlImage->fileName, $htmlImage->mimeType, basename($htmlImage->fileName), $isfile = true, $htmlImage->contentId
				);
			}
			//do not ever try to call these lines in reverse order
			/*
			  $param["text_encoding"] - Type of encoding to use for the plain text part of the email. Default is "7bit".
			  $param["html_encoding"] - Type of encoding for the HTML part of the email. Default is "quoted-printable".
			  $param["7bit_wrap"] - Number of characters after which text is wrapped. SMTP stipulates maximum line length of 1000 characters including CRLF. Default is 998 (CRLF is appended to make up to 1000).
			  $param["head_charset"] - The character set to use for the headers. Default is "iso-8859-1".
			  $param["text_charset"] - The character set to use for the plain text part of the email. Default is "iso-8859-1".
			  $param["html_charset"]
			 */
			$body = $mime->get(
							array(
								'head_charset' => 'utf-8',
								'text_charset' => 'utf-8',
								'html_charset' => 'utf-8'
							)
			);
			$hdrs = $mime->headers($hdrs);
		} else {
			// plain
			if (empty($html)) {
				$body = $plaintext;
			} else {
				$body = $html;
			}
			if (!isset($headers['Content-Type'])) {
				$headers['Content-Type'] = 'text/plain; charset=utf-8;';
			}
		}

		if (self::$enabled) {
			$success = $this->mailer->send($to, $hdrs, $body);
		} else {
			trigger_error('disabled sendMail for ' . $to);
			$success = true;
		}

		// catch pear errors
		if ($success === true) {
			$this->lastError = '';
			$success = true;
		} else {
			$this->lastError = $success->getMessage();
			$success = false;
		}
		if (self::$logLast) {
			self::$lastSuccess = $success;
			self::$lastSubject = $subject;
			
			self::$lastHtml = $html;
			self::$lastPlain = $plaintext;
			self::$lastTo = $to;
			self::$lastHeaders = $hdrs;
			$mailLog = Config::getLogDir(\Foomo\Module::NAME) . DIRECTORY_SEPARATOR . 'mail_log';
			$fp = fopen($mailLog, 'a+');
			$logEntry =
					PHP_EOL .
					'---------------------------------------------------------------------------' . PHP_EOL .
					'-- ' . __CLASS__ . ' (' . date('Y-m-d H:i:s') . ') sending mail to ' . $to . PHP_EOL .
					'---------------------------------------------------------------------------' . PHP_EOL .
					'-- SUBJECT ----------------------------------------------------------------' . PHP_EOL .
					$subject . PHP_EOL .
					'-- HEADERS ----------------------------------------------------------------' . PHP_EOL .
					var_export($headers, true) .
					'-------- PLAIN ------------------------------------------------------------' . PHP_EOL .
					$plaintext . PHP_EOL .
					'-- HTML -------------------------------------------------------------------' . PHP_EOL .
					$html . PHP_EOL .
					'---------------------------------------------------------------------------' . PHP_EOL
			;
			fwrite($fp, $logEntry);
			fclose($fp);
		}
		return $success;
	}

	/**
	 * get the last error as a string - currently that is the message from the wrapped pear class
	 *
	 * @return string
	 */
	public function getLastError()
	{
		return $this->lastError;
	}

}