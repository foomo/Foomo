<?php

/*
 * This file is part of the foomo Opensource Framework.
 *
 * The foomo Opensource Framework is free software: you can redistribute it
 * and/or modify it under the terms of the GNU Lesser General Public License as
 * published  by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * The foomo Opensource Framework is distributed in the hope that it will
 * be useful, but WITHOUT ANY WARRANTY; without even the implied warranty
 * of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License along with
 * the foomo Opensource Framework. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Foomo\Http;

/**
 * handle the 304 not modified browser cache features documented in this RFC
 *
 * http://www.w3.org/Protocols/rfc2616/rfc2616-sec13.html#sec13.3.3
 *
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 * @internal do not use this one yet
 */
class BrowserCache {
	/*
	 * the ETag is weak and can thus be used for a range of resources
	 *
	 * @var boolean
	 * private $weak;
	 */

	/**
	 * entity tag
	 *
	 * @var string
	 */
	private $ETag;
	/**
	 * content type
	 *
	 * @var string
	 */
	private $contentType;
	/**
	 * last modification time of the resource
	 *
	 * @var integer
	 */
	private $lastModified;
	/**
	 * configuration - when to expire unix timestamp - for all instances
	 *
	 * @var integer
	 */
	public $expiry;
	/**
	 * configuration - maximal age in seconds
	 *
	 * @var integer
	 */
	public $maxAge;

	private function __construct() {}

	/**
	 * private singleton
	 *
	 * @return BrowserCache
	 */
	private static function getInstance()
	{
		static $instance;
		if (!$instance) {
			$instance = new self();
		}
		return $instance;
	}

	/**
	 * set the resource defining data
	 *
	 * @param string $contentType mime type of the content
	 * @param string $ETag entity tag
	 * @param integer $lastModified unix timestamp of last modification
	 * @param integer $maxAge how many seconds may the object live
	 * @param integer $expiry unix timestamp when the object will expire
	 */
	public static function setResourceData($contentType = null, $ETag = null, $lastModified = null, $maxAge = null, $expiry = null)
	{
		$inst = self::getInstance();
		$inst->contentType = $contentType;
		$inst->ETag = $ETag;
		$inst->lastModified = $lastModified;
		$inst->maxAge = $maxAge;
		$inst->expiry = $expiry;
	}

	/**
	 * check if the browser has a valid matching cache entry
	 *
	 * From http://www.w3.org/Protocols/rfc2616/rfc2616-sec13.html#sec13.3.4
	 *
	 * - SHOULD send an entity tag validator unless it is not feasible to
	 * 	 generate one.
	 *
	 * - MAY send a weak entity tag instead of a strong entity tag, if
	 * 	 performance considerations support the use of weak entity tags,
	 * 	 or if it is unfeasible to send a strong entity tag.
	 *
	 * - SHOULD send a Last-Modified value if it is feasible to send one,
	 * 	 unless the risk of a breakdown in semantic transparency that
	 * 	 could result from using this date in an If-Modified-Since header
	 * 	 would lead to serious problems.
	 *
	 *
	 * @return boolean true if the browser has a valid cache entry or false, if not
	 */
	public static function tryBrowserCache()
	{
		$inst = self::getInstance();
		return $inst->tryIt();
	}

	private function tryIt()
	{
		// If-Modified-Since
		// If-Unmodified-Since
		if (isset($this->ETag)) {
			$browserETag = $this->getHeader('If-None-Match');
			if ($browserETag == $this->ETag) {
				if (isset($this->lastModified)) {
					$candidates = array($this->getHeader('If-Modified-Since'), $this->getHeader('If-Unmodified-Since'));
					foreach ($candidates as $candidate) {
						if (!is_null($candidate)) {
							//trigger_error('checking modification ' . strtotime($candidate) . ' comparing with ' . $this->lastModified);
							$candidate = strtotime($candidate);
							if ($candidate != $this->lastModified) {
								return false;
							} else {
								break;
							}
						}
					}
				}
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * get a header
	 *
	 * @param string $name
	 * @return string
	 */
	private function getHeader($name)
	{
		if (isset($_SERVER[$name])) {
			return $_SERVER[$name];
		} elseif (function_exists('apache_request_headers')) {
			static $allHeaders;
			if (!isset($allHeaders)) {
				$allHeaders = apache_request_headers();
			}
			if (isset($allHeaders[$name])) {
				return $allHeaders[$name];
			}
		}
	}

	/**
	 * get a date, that complies to HTTP header standards
	 *
	 * @param integer $time a unix timestamp
	 * @return string a formatted date
	 * @internal
	 */
	public static function getDate($time)
	{
		$def = date_default_timezone_get();
		date_default_timezone_set('Europe/London');
		$ret = date('D, d M Y H:i:s T', $time);
		date_default_timezone_set($def);
		return $ret;
	}

	/**
	 * send a "304 Not Modified"
	 *
	 */
	public static function sendNotModified()
	{
		header($_SERVER['SERVER_PROTOCOL'] . ' 304 Not Modified');
		/*
		  string session_cache_limiter ([ string $cache_limiter ] )

		  session_cache_limiter() returns the name of the current cache limiter.

		  The cache limiter defines which cache control HTTP headers are sent to the client.
		  These headers determine the rules by which the page content may be cached by the client
		  and intermediate proxies. Setting the cache limiter to nocache disallows any
		  client/proxy caching. A value of public permits caching by proxies and the client,
		  whereas private disallows caching by proxies and permits the client to cache the contents.

		  In private mode, the Expire header sent to the client may cause confusion for some browsers,
		  including Mozilla. You can avoid this problem by using private_no_expire mode.
		  The expire header is never sent to the client in this mode.

		  The cache limiter is reset to the default value stored in session.cache_limiter at
		  request startup time. Thus, you need to call session_cache_limiter() for every request
		  (and before session_start() is called).

		 */

		//session_cache_limiter('public');
		self::sendHeaders();
	}

	/**
	 * send headers
	 *
	 * @param integer $contentLength
	 */
	public static function sendHeaders($contentLength = null)
	{
		$inst = self::getInstance();
		if (!is_null($contentLength)) {
			header('Content-Length: ' . $contentLength);
		}
		/*
		  if(isset($inst->pragma)) {
		  header('Pragma: '.$inst->pragma);
		  }
		 */
		header('Pragma: cache');
		if (isset($inst->expiry)) {
			header('Expires: ' . self::getDate($inst->expiry));
		} else {
			header('Expires: ' . self::getDate(time() + $inst->maxAge));
		}

		if (isset($inst->ETag)) {
			header('Etag: ' . $inst->ETag);
		}

		if (isset($inst->lastModified)) {
			//trigger_error('last mod 000000====' . $inst->lastModified);
			//header('Last-Modified: '. self::getDate($inst->lastModified));
			header('Last-Modified: ' . self::getDate($inst->lastModified));
		}
		if (isset($inst->maxAge)) {
			header('Cache-Control: max-age=' . $inst->maxAge . ', private');
		}
	}

}