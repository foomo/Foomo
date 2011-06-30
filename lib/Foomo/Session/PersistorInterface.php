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

namespace Foomo\Session;

/**
 * implement this to get your own session persistors
 * 
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
interface PersistorInterface {
	/**
	 * this has to be an atomic lock across requests and possibly across
	 * machines
	 *
	 * @param string $sessionId
	 */
	public function lock($sessionId);
	/**
	 * does a session exist
	 *
	 * @param string $sessionId
	 */
	public function exists($sessionId);
	/**
	 * load a session
	 *
	 * @param string $sessionId
	 *
	 * @return Foomo\Session
	 */
	public function load($sessionId);
	/**
	 * release the atomic lock from a session
	 *
	 * @param string $sessionId
	 */
	public function release($sessionId);
	/**
	 * destroy a session
	 *
	 * @param string $sessionId
	 */
	public function destroy($sessionId);
	/**
	 * persist a session
	 *
	 * @param string $sessionId
	 * @param \Foomo\Session $session
	 *
	 */
	public function persist($sessionId, \Foomo\Session $session);
}