<?php

/*
 * bestbytes-copyright-placeholder
 */

namespace Foomo\Cache\Persistence\Queryable;

class MongoPersistorIterator extends \Foomo\Cache\CacheResourceIterator {

	private $mongoCursor;

	public function __construct($cursor) {
		$this->mongoCursor = $cursor;
	
		if ( $this->mongoCursor->count() > 0) {
			$this->mongoCursor->next();
			
		}
	}

	/**
	 * @return Foomo\Cache\CacheResource
	 */
	public function current() {
		$document = $this->mongoCursor->current();
		return MongoPersistor::mapDocumentToResource($document);
	}

	public function next() {
		if ($this->mongoCursor->count() > 0)return $this->mongoCursor->next();
	}

	public function key() {
		return $this->mongoCursor->key;
	}

	public function valid() {
		return $this->mongoCursor->valid();
	}

	public function rewind() {
		if ($this->mongoCursor->count() > 0) return $this->mongoCursor->rewind();
		else return false;
	}

	public function count() {
		return $this->mongoCursor->count();
	}

}