<?php

namespace Foomo\Cache\MockObjects;

class Node {
	const PREFIX = 'node-';
	public $id;
	public $parentId;
	public $name;

	/**
	 * array of ids of nested leaves
	 *
	 * @var string[]
	 */
	public $childNodeIds = array();
	/**
	 * array of ids of parent leaves up to the root node
	 *
	 * @var string[]
	 */
	public $path = array();

	public function __construct($parentId, $name)
	{
		$this->parentId = $parentId;
		$this->name = $name;
		$this->save();
	}

	public function save()
	{
		// invalidate caches from here
		$this->path = array();
		self::crawlPath($this, $this->path);

		$parentId = $this->id;
		$childNodeIds = array();
		$childNodeFunc = 

		self::iterateAllNodesWithFunction(function(Node $node) use ($parentId, $childNodeIds) {
			if($node->id == $parentId) {
				$childNodeIds[] = $node->id;
			}
			return false;
		});

		$this->childNodeIds = $childNodeIds;

		if(!isset($this->id)) {
			$tempnam = \tempnam(self::getFSResource()->getFileName(), self::PREFIX);
			$this->id = \substr(\basename($tempnam), \strlen(self::PREFIX));
		}
		\file_put_contents(self::getFSResource()->getFileName() . \DIRECTORY_SEPARATOR . self::PREFIX . $this->id, \serialize($this));
	}
	/**
	 * @return \Foomo\Modules\Resource\Fs
	 */
	public static function getFSResource()
	{
		$resource = \Foomo\Modules\Resource\Fs::getVarResource(\Foomo\Modules\Resource\Fs::TYPE_FOLDER, 'nodeMockObject');
		if(!$resource->resourceValid()) {
			$resource->tryCreate();
		}
		return $resource;
	}

	private static function crawlPath(Node $node, &$pathArray)
	{
		$pathArray[] = $node->parentId;
		$parentNode = self::getNode($node->parentId);
		if($parentNode) {
			self::crawlPath($parentNode, $pathArray);
		}
	}

	/**
	 * I want to be cached for the future
	 *
	 * @param string $id
	 *
	 * @return Foomo\Cache\MockObjects\Node
	 */
	public static function getNode($id)
	{
		if(!empty($id)) {
			$directoryIterator = new \DirectoryIterator(self::getFSResource()->getFileName());
			$ret = null;
			self::iterateAllNodesWithFunction(function(Node $node) use($ret) {
				if($node->id == $id) {
					$ret = $node;
					return true;
				}
			});
			return $ret;
		}
	}
	
	private static function iterateAllNodesWithFunction($func)
	{
		$directoryIterator = new \DirectoryIterator(self::getFSResource()->getFileName());
		foreach($directoryIterator as $file) {
			/* @var $file \SplFileInfo */
			if($file->isFile() && \substr($file->getBasename(),0, strlen(self::PREFIX)) == self::PREFIX) {
				if(!false === ($node = \unserialize(\file_get_contents($file->getFilename()))) ) {
					if($func($node)) {
						return;
					}
				}
			}
		}
	}
}