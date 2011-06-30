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

namespace Foomo\Config;

/**
 * struct representing a deleted config
 * 
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
class OldConfig {
	const TYPE_DELETED = 'deleted';
	const TYPE_BACKUP = 'backup';
	/**
	 * @var string
	 */
	public $id;
	/**
	 * type one of self::TYPE_...
	 *
	 * @var string
	 */
	public $type;
	/**
	 * @var string
	 */
	public $filename;
	/**
	 * @var integer
	 */
	public $timestamp;
	/**
	 * @var string
	 */
	public $module;
	/**
	 * @var string
	 */
	public $name;
	/**
	 * @var type
	 */
	public $domain;
}