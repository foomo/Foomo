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