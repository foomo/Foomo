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

namespace Foomo\Modules;

use Foomo\MVC\AbstractApp;

/**
 * manage modules
 * 
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
class Frontend extends AbstractApp
{
	//---------------------------------------------------------------------------------------------
	// ~ Constants
	//---------------------------------------------------------------------------------------------

	public function __construct()
	{
		$HTMLDoc = \Foomo\HTMLDocument::getInstance();
		$HTMLDoc->addJavascripts(array(\Foomo\ROOT_HTTP . '/js/jquery-1.6.1.min.js'));
		$HTMLDoc->addJavascripts(array(\Foomo\ROOT_HTTP . '/js/modules.js'));
		parent::__construct(get_class($this));
	}
}