<?php

/*
 * bestbytes-copyright-placeholder
 */

namespace Foomo\Mailer;

/**
 * image for a mail
 */
class HTMLImage {

	/**
	 * file name
	 *
	 * @var string
	 */
	public $fileName;
	/**
	 * url for the image
	 * 
	 * @var string
	 */
	public $url;
	/**
	 * mime type
	 *
	 * @var string
	 */
	public $mimeType;
	/**
	 * name
	 *
	 * @var string
	 */
	public $name;
	/**
	 * cid
	 *
	 * @var string
	 */
	public $contentId;
	public function __construct()
	{
		$this->contentId = uniqid();
	}

}