<?php

namespace Foomo\Http;

/**
 * a mock
 *
 */
class MockServer {
	const ETAG_LAST_MODIFIED_RESOURCE = 'last-mdfd-rs';
	const CONTROLLER_ID = 'mockiMocki';
	public function actionDefault()
	{
		echo 'haaaaaaaaa';
	}
	public function actionLastModified($lastModified)
	{
		header('Content-type: text/plain');
		BrowserCache::setResourceData('text/plain', self::ETAG_LAST_MODIFIED_RESOURCE, $lastModified, 5*24*3600);
		if(!BrowserCache::tryBrowserCache()) {
			$out = 'resource, that was last modified : ' . $lastModified . ' and its ETag is ' . self::ETAG_LAST_MODIFIED_RESOURCE;
			BrowserCache::sendHeaders(strlen($out));
			echo $out;
		} else {
			BrowserCache::sendNotModified();
		}
		exit;
	}
	public function actionModified()
	{
		echo 'na';
	}
}