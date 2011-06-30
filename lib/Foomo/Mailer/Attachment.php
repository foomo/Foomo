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

namespace Foomo\Mailer;

/**
 * a mail attachment
 * 
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
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