<?php

/*
 * bestbytes-copyright-placeholder
 */

namespace Foomo\Frontend;

/**
 * toolbox model
 */
class Model {

	/**
	 * what info to display
	 * 
	 * @var string
	 */
	public $currentInfo;
	public $currentFrameUrl = 'about:blank';
	
	public $classMap;

	/**
	 * show an info
	 *
	 * @param string $type
	 * @param string $block
	 */
	public function showInfo($type, $block = null)
	{
		switch ($type) {
			case'php':
				if ($block) {
					phpinfo($block);
				} else {
					phpinfo();
				}
				exit;
			case'APC':
				header('Location: ' . \Foomo\ROOT_HTTP . '/apece.php');
				exit;
			case'Memcache':
				header('Location: ' . \Foomo\ROOT_HTTP . '/memcache.php');
				exit;
		}
	}

}