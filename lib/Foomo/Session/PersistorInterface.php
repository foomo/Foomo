<?php

namespace Foomo\Session;

interface PersistorInterface {
	public function lock($sessionId);
	public function exists($sessionId);
	public function load($sessionId, $reload = false);
	public function release($sessionId);
	public function destroy($sessionId);
	public function persist($sessionId, $sessionObj);
}