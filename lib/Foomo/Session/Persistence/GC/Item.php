<?php

namespace Foomo\Session\Persistence\GC;

/**
 * a GCollectable item
 */
class Item {
	/**
	 * timestamp
	 * 
	 * @var float
	 */
	public $lastReadAccess = 0;
	/**
	 * timestamp
	 * 
	 * @var float
	 */
	public $lastWriteAccess = 0;
	/**
	 * @var string
	 */
	public $sessionId;
}