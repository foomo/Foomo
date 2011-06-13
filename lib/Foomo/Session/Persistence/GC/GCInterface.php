<?php

namespace Foomo\Session\Persistence\GC;
/**
 * an iterator over your session persistor
 */
interface GCInterface extends \Iterator {
	public function __construct(\Foomo\Session\GCPrinterInterface $gcPrinter);
}