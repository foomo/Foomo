<?php

/*
 * bestbytes-copyright-placeholder
 */

namespace Foomo\Cache\Persistence\Queryable;

class MongoPersistorIterator extends \Foomo\Cache\CacheResourceIterator {

	private $mongoCursor;

	public function __construct($cursor)
	{
		$this->mongoCursor = $cursor;
		$this->mongoCursor->next();
	}

	/**
	 * @return Foomo\Cache\CacheResource
	 */
	public function current()
	{
		$document = $this->mongoCursor->current();
		return MongoPersistor::mapDocumentToResource($document);
	}

	public function next()
	{
		return $this->mongoCursor->next();
	}

	public function key()
	{
		return $this->mongoCursor->key;
	}

	public function valid()
	{
		return $this->mongoCursor->valid();
	}

	public function rewind()
	{
		return $this->mongoCursor->rewind();
	}

	public function count()
	{
		return $this->mongoCursor->count();
	}

}