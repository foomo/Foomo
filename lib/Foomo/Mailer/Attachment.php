<?php

/*
 * bestbytes-copyright-placeholder
 */

namespace Foomo\Mailer;

/**
 * a mail attachment
 */
class Attachment {

	/**
	 * The file name of the file to attach
	 *
	 * @var string
	 */
	public $fileName;
	/**
	 * mime type
	 *
	 * @var string
	 */
	public $mimeType = 'application/octet-stream';
	/**
	 * name
	 *
	 * @var string
	 */
	public $name = '';
	/**
	 * language
	 * 
	 * @var string
	 */
	public $language = '';
	/**
	 * description
	 *
	 * @var string
	 */
	public $description = '';

	const DISPOSITION_ATTACHMENT = 'attachment';
	const DISPOSITION_INLINE = 'inline';
	/**
	 * The content-disposition of this file. Defaults to attachment. Possible values: attachment, inline.
	 *
	 * @var string
	 */
	public $disposition = 'attachment';
	/**
	 * location
	 * 
	 * @var string
	 */
	public $location;
	/**
	 * charset
	 *
	 * @var string
	 */
	public $charset = '';
}