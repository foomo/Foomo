<?php

namespace Foomo\Session;

/**
 * implement this to get your own session persistors
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