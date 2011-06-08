<?php

/*
 * bestbytes-copyright-placeholder
 */

namespace Foomo\Log\Plot;

use Foomo\Log\Entry;

class Plotter {
	const DATA_SUFFIX = 'data';
	const GNU_PLOT_SUFFIX = 'gnu';
	const MODE_NO_SESSION = 'noSession';
	const MODE_SESSION = 'session';
	public $firstRequest;
	public $lastRequest;
	public $mode;
	/**
	 * @var Foomo\Modules\Resource\Fs
	 */
	private $workDir;
	/**
	 * @var Foomo\Modules\Resource\Fs
	 */
	private $dataDir;
	public $sessions = array();
	private $logFile;
	private $entryPlotterModifier;
	public function __construct($mode, $logFile, $entryPlotterModifier = null)
	{
		$this->workDir = \Foomo\Modules\Resource\Fs::getVarResource(\Foomo\Modules\Resource\Fs::TYPE_FOLDER, 'loggerPlot');
		$this->dataDir = \Foomo\Modules\Resource\Fs::getVarResource(\Foomo\Modules\Resource\Fs::TYPE_FOLDER, 'loggerPlot/data');

		$this->cleanUp();

		$this->entryPlotterModifier = $entryPlotterModifier;
		$this->logFile = $logFile;
		if (!in_array($mode, $modes = array(self::MODE_NO_SESSION, self::MODE_SESSION))) {
			throw new \InvalidArgumentException($mode . ' is not one of ' . implode(', ', $modes));
		}
		$this->mode = $mode;
		// set up the workspace
		if (!$this->workDir->tryCreate()) {
			trigger_error('could not create work dir', E_USER_ERROR);
		}
		// data dir
		if (!$this->dataDir->tryCreate()) {
			trigger_error('could not create data dir', E_USER_ERROR);
		}
	}

	private $handleForAll;

	public function addEntry(EntryPlotter $entryPlotter)
	{
		$data = $entryPlotter->plot() . PHP_EOL;
		if ($this->mode == self::MODE_NO_SESSION) {
			if (!isset($this->handleForAll)) {
				$this->handleForAll = fopen($this->getAllDataFile(), 'a+');
			}
			fwrite($this->handleForAll, $data);
		} else {
			if (!in_array($entryPlotter->entry->sessionId, $this->sessions)) {
				$this->sessions[] = $entryPlotter->entry->sessionId;
			}
			$fh = fopen($this->getSessionFilename($entryPlotter->entry->sessionId), 'a+');
			fwrite($fh, $data);
			fclose($fh);
		}
	}

	public function getSessionFilename($sessionId)
	{
		return $this->dataDir->getFileName() . \DIRECTORY_SEPARATOR . 'session-' . $sessionId . '.' . self::DATA_SUFFIX;
	}

	public function getAllDataFile()
	{
		return $this->dataDir->getFileName() . \DIRECTORY_SEPARATOR . 'all.' . self::DATA_SUFFIX;
	}

	public function plot($filterFunction = null)
	{

		$reader = new \Foomo\Log\Reader($this->logFile);
		if ($filterFunction) {
			$reader->setFilter($filterFunction);
		}
		$this->lastRequest = 0;
		/* @var $entry Entry */
		foreach ($reader as $entry) {
			echo $entry->id . PHP_EOL;
			$entryPlotter = new EntryPlotter($entry);
			if (isset($this->entryPlotterModifier)) {
				call_user_func_array($this->entryPlotterModifier, array($entryPlotter));
			}
			$this->addEntry($entryPlotter);
			if ($this->lastRequest < $entry->logTime) {
				$this->lastRequest = (int) $entry->logTime;
			}
			if (!isset($this->firstRequest)) {
				$this->firstRequest = (int) $entry->logTime;
			}
			if ($this->firstRequest > $entry->logTime) {
				$this->firstRequest = (int) $entry->logTime;
			}
		}

		if ($this->mode == self::MODE_NO_SESSION) {
			$view = \Foomo\Module::getView($this, 'all', $this);
		} else {
			$view = \Foomo\Module::getView($this, 'sessions', $this);
		}
		$setupView = \Foomo\Module::getView($this, 'setup', $this);
		$reportPlotName = $this->workDir->getFileName() . \DIRECTORY_SEPARATOR . 'logger.' . self::GNU_PLOT_SUFFIX;
		file_put_contents($reportPlotName, $setupView->render() . PHP_EOL . $view->render());
	}

	private function cleanUp()
	{
		// cleanup session data
		if (file_exists($dataDir = $this->dataDir->getFileName())) {
			$iterator = new \FilesystemIterator($dataDir);
			foreach ($iterator as $file) {
				if ($file->isFile() && !$file->isDir()) {
					if (substr($file->getFilename(), 0, 8) == 'session-') {
						\unlink($file->getPathname());
					}
				}
			}
		}
		if (file_exists($this->getAllDataFile())) {
			unlink($this->getAllDataFile());
		}
	}

}