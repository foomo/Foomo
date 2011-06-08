<?php

/*
 * bestbytes-copyright-placeholder
 */

namespace Foomo\Modules\Resource;

/**
 * a file system resource
 *
 */
class Fs extends \Foomo\Modules\Resource {
	const TYPE_SOCKET = 'socket';
	const TYPE_FIFO = 'fifo';
	const TYPE_FOLDER = 'dir';
	const TYPE_FILE = 'file';
	/**
	 * type of fs resource - one of self::TYPE_...
	 *
	 * @var string
	 */
	private $type;
	/**
	 * Enter description here...
	 *
	 * @var string
	 */
	private $filename;
	private function __construct($type, $filename)
	{
		if (!in_array($type, array(self::TYPE_FILE, self::TYPE_FOLDER))) {
			throw new InvalidArgumentException();
		}
		$this->type = $type;
		$this->filename = $filename;
	}

	public static function getAbsoluteResource($type, $fileName)
	{
		return new self($type, $fileName);
	}

	/**
	 * get the file name
	 *
	 * @return string
	 */
	public function getFileName()
	{
		return $this->filename;
	}

	/**
	 * get a FS resource that is relative to \Foomo\ROOT/var/currentRunMode 
	 *
	 * @param string $type one of self::TYPE_... 
	 * @param string $relFilename relative filename
	 * 
	 * @return Foomo\Modules\Resources\Fs
	 */
	public static function getVarResource($type, $relFilename)
	{
		return new self($type, \Foomo\Config::getVarDir() . DIRECTORY_SEPARATOR . $relFilename);
	}

	/**
	 * self::getDynamicHtdocsResource(self::TYPE_FOLDER, 'foo', 'css') . DIRECTORY_SEPARATOR . 'bar.css' =>
	 * 
	 * /r/modulesVar/foo/css/bar.css
	 * 
	 * @param string $type one of self::TYPE_... 
	 * @param string $module name of the module
	 * @param string $relFileName 
	 * 
	 * @return Foomo\Modules\Resources\Fs
	 */
	public static function getModuleHtdocsVarResource($type, $module, $relFileName)
	{
		return new self($type, \Foomo\Config::getHtdocsVarDir($module) . DIRECTORY_SEPARATOR . $relFileName);
	}

	/**
	 * get a logging resource
	 * 
	 * @param string $type
	 * @param string $relFilename relative filename
	 * @param string $module name of the module
	 * 
	 * @return Foomo\Modules\Resources\Fs
	 */
	public static function getLogResource($type, $relFilename, $module = 'Foomo') // $module = \Foomo\Module::NAME
	{
		return new self($type, \Foomo\Config::getLogDir($module) . DIRECTORY_SEPARATOR . $relFilename);
	}

	/**
	 * get a FS resource that is relative to \Foomo\ROOT/var/currentRunMode/currentCache 
	 *
	 * @param string $type one of self::TYPE_... 
	 * @param string $relFilename relative filename
	 * 
	 * @return Foomo\Modules\Resources\Fs
	 */
	public static function getCacheResource($type, $relFilename)
	{
		return new self($type, \Foomo\Config::getCacheDir() . DIRECTORY_SEPARATOR . $relFilename);
	}

	public function resourceValid()
	{
		if (file_exists($this->filename)) {
			if (filetype($this->filename) == $this->type) {
				return true;
			} else {
				if ($this->type == 'file' && filetype($this->filename) == 'link' && is_file($this->filename)) {
					return true;
				}
				if ($this->type == 'dir' && filetype($this->filename) == 'link' && is_dir($this->filename)) {
					return true;
				}
			}
		}
		return false;
	}

	public function resourceStatus()
	{
		$ret = 'File resource "' . $this->filename . '" of type : "' . $this->type . '"';
		if (!$this->resourceValid()) {
			if (file_exists($this->filename)) {
				return $ret . ' is of unexpected type "' . filetype($this->filename) . '"';
			} else {
				return $ret . ' is missing';
			}
		} else {
			return $ret . ' is ok';
		}
	}

	public function tryCreate()
	{
		$ret = '';
		switch ($this->type) {
			case self::TYPE_FOLDER:
				$ret .= 'trying to create folder ' . $this->filename;
				if (!file_exists($this->filename)) {
					if ($this->tryCreateFolder($this->filename)) {
						$ret .= ' success';
					} else {
						$ret .= ' failed';
					}
				} else {
					$ret .= ' folder exists';
				}
				break;
			case self::TYPE_FILE:
				$ret .= 'trying to create file ' . $this->filename;
				if (!file_exists($this->filename)) {
					$dirname = dirname($this->filename);
					if (!file_exists($dirname)) {
						$ret .= ' trying to create parent folder';
						if (@$this->tryCreateFolder($dirname)) {
							$ret .= ' - success';
						} else {
							$ret .= '- failed : ' . error_get_last();
						}
					}
					if (!is_writable($dirname)) {
						if (!file_exists($dirname)) {
							$ret .= ' no parent folder';
						} else {
							$ret .= ' parent folder is not writable';
						}
					} else {
						if (touch($this->filename)) {
							$ret .= ' - success';
						} else {
							$ret .= ' - failed';
						}
					}
				}
				break;
			default:
				$ret .= 'can not automatically create a resource of type ' . $this->type;
		}
		return $ret;
	}

	private function tryCreateFolder($dirname)
	{
		$i = 0;
		$oldDirname = null;
		$existingFolder = null;
		$todo = array();
		$file = $dirname;
		while (($dirname = dirname($file)) && $dirname != $oldDirname && $i < 101) {
			if ($i >= 100) {
				trigger_error('try create folder seems to have a bug on your system ... ', E_USER_WARNING);
				return false;
			} else {
				$i++;
			}
			$todo[] = basename($file);
			$oldDirname = $dirname;
			$i++;
			$file = $dirname;
			if (file_exists($file) && is_dir($file)) {
				$existingFolder = $file;
				$todo = array_reverse($todo);
				break;
			}
		}
		if ($existingFolder && is_writable($existingFolder)) {
			foreach ($todo as $folderName) {
				$newFolder = $existingFolder . DIRECTORY_SEPARATOR . $folderName;
				if (mkdir($newFolder)) {
					$existingFolder = $newFolder;
				} else {
					return false;
				}
			}
		}
		return true;
	}

}