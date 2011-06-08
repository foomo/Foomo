<?php

/*
 * bestbytes-copyright-placeholder
 */

namespace Foomo\Log\Plot;

use \Foomo\Log\Entry;

class EntryPlotter {

	/**
	 * color of the entry
	 *
	 * @var int
	 */
	public $color = 0x000000;
	/**
	 * entry to plot
	 * 
	 * @var Entry
	 */
	public $entry;
	public $label = '';
	public function __construct(Entry $entry)
	{
		$this->entry = $entry;
	}

	public function plot()
	{
		$data = \Foomo\Module::getView('Foomo\\Log' , 'entry', $this)->render();
		return $data;
	}

	public function getErrorColor()
	{
		if ($this->entry->exception) {
			return 0xff0000;
		}
		return 0x00ff00;
		foreach ($this->entry->phpErrors as $phpError) {
			$phpError['no'];
		}
	}

}