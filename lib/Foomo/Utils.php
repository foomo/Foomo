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

namespace Foomo;

/**
 * solves very common problems
 *
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
class Utils
{

	/**
	 * Exposes a file through the web server and streams it
	 * Note: creates symlinks exposedDirecytory/exposedFile -> fileName
	 *
	 * @todo implement var/<runmode>/htdocs/moduleVar dir use
	 *
	 * @param string $fileName the full path to the file to be exposed
	 * @param string $exposeDirectory a folder relative to \Foomo\ROOT in which the file will be exposed
	 * @param string $exposedBaseFileName the exposed file (base) name. If null - basename from $fileName is used
	 */
	public static function exposeAndStreamFile($fileName, $exposeDirectory, $exposedBaseFileName = null)
	{

		$downloadUrl = 'http://' . $_SERVER['SERVER_NAME'] . \Foomo\ROOT_HTTP . '/' . $exposeDirectory;
		//\trigger_error('download URL ' . $downloadUrl);



		if (\is_null($exposedBaseFileName)) {
			$linkFileName = \basename($fileName);
		} else {
			$linkFileName = $exposedBaseFileName;
		}

		$dir = \Foomo\ROOT . DIRECTORY_SEPARATOR . 'htdocs' . DIRECTORY_SEPARATOR . $exposeDirectory;

		if (!\file_exists($dir)) {
			\mkdir($dir);
		}

		$link = $dir . DIRECTORY_SEPARATOR . $linkFileName;
		if (!\file_exists($link)) {
			\symlink($fileName, $link);
		}
		header('Location: ' . $downloadUrl . '/' . $linkFileName);
	}

	/**
	 * check if a path is absolute or not
	 *
	 * @return boolean
	 */
	public static function isAbsolutePath($path)
	{
		if (realpath($path) != $path) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * stream a file
	 *
	 * @param string $filename
	 * @param string $downloadName
	 * @param string $mimeType like image/jpeg
	 * @param boolean $toBeDownloaded should the file offered as a download
	 *
	 * @return boolean
	 */
	public static function streamFile($filename, $downloadName = null, $mimeType = 'application/octet-stream', $toBeDownloaded = false)
	{
		if (!file_exists($filename)) {
			trigger_error('can not stream file - file does not exist "' . $filename . '"', E_USER_WARNING);
			return false;
		}
		set_time_limit(0); // Reset time limit for big files
		session_cache_limiter('public');
		header('Cache-Control: cache');
		$fSize = fileSize($filename);
		header('Content-Length: ' . $fSize);

		if (!isset($mimeType)) {
			$mimeType = self::guessMime($filename);
		}
		header('Content-Type: ' . $mimeType);
		header('Accept-Ranges: bytes');

		if (isset($downloadName) AND $toBeDownloaded === true) {
			header('Content-Disposition: attachment; filename="' . addslashes($downloadName) . '"');
		}
		if (isset($_SERVER['HTTP_RANGE'])) {
			//\trigger_error($_SERVER['HTTP_RANGE']);
			//do the range download
			return self::rangeDownload($filename);
		} else {
			while (ob_get_level() > 0) {
				ob_end_clean();
			}
			$fp = fopen($filename, 'r');
			fpassthru($fp);
			fclose($fp);
			return true;
		}
	}

	private static function rangeDownload($file)
	{
		$fp = @fopen($file, 'rb');

		$size = filesize($file); // File size
		$length = $size;  // Content length
		$start = 0;   // Start byte
		$end = $size - 1; // End byte
		// Now that we've gotten so far without errors we send the accept range header
		/* At the moment we only support single ranges.
		 * Multiple ranges requires some more work to ensure it works correctly
		 * and comply with the spesifications: http://www.w3.org/Protocols/rfc2616/rfc2616-sec19.html#sec19.2
		 *
		 * Multirange support annouces itself with:
		 * header('Accept-Ranges: bytes');
		 *
		 * Multirange content must be sent with multipart/byteranges mediatype,
		 * (mediatype = mimetype)
		 * as well as a boundry header to indicate the various chunks of data.
		 */
		// multipart/byteranges
		// http://www.w3.org/Protocols/rfc2616/rfc2616-sec19.html#sec19.2

		if (isset($_SERVER['HTTP_RANGE'])) {

			$c_start = $start;
			$c_end = $end;
			// Extract the range string
			list(, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);
			$vals = explode('=', $_SERVER['HTTP_RANGE'], 2);

			//\trigger_error($_SERVER['HTTP_RANGE'] . \var_export($vals, true));
			// Make sure the client hasn't sent us a multibyte range
			if (strpos($range, ',') !== false) {

				// (?) Shoud this be issued here, or should the first
				// range be used? Or should the header be ignored and
				// we output the whole content?
				header('HTTP/1.1 416 Requested Range Not Satisfiable');
				header("Content-Range: bytes $start-$end/$size");
				// (?) Echo some info to the client?
				return false;
			}
			// If the range starts with an '-' we start from the beginning
			// If not, we forward the file pointer
			// And make sure to get the end byte if spesified
			if ($range[0] == '-') {
				// The n-number of the last bytes is requested
				$c_start = $size - substr($range, 1);
			} else {
				$range = explode('-', $range);
				$c_start = $range[0];
				$c_end = (isset($range[1]) && is_numeric($range[1])) ? $range[1] : $size;
			}
			/* Check the range and make sure it's treated according to the specs.
			 * http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html
			 */
			// End bytes can not be larger than $end.
			$c_end = ($c_end > $end) ? $end : $c_end;
			// Validate the requested range and return an error if it's not correct.
			if ($c_start > $c_end || $c_start > $size - 1 || $c_end >= $size) {

				\header('HTTP/1.1 416 Requested Range Not Satisfiable');
				\header("Content-Range: bytes $start-$end/$size");
				// (?) Echo some info to the client?
				\trigger_error(__METHOD__ . ' download range not valid', E_USER_WARNING);
				return false;
			}
			$start = $c_start;
			$end = $c_end;
			$length = $end - $start + 1; // Calculate new content length
			\fseek($fp, $start);
			\header('HTTP/1.1 206 Partial Content');
		}
		// Notify the client the byte range we'll be outputting
		\header("Content-Range: bytes $start-$end/$size");
		\header("Content-Length: $length");
		// Start buffered download
		$buffer = 1024 * 8;
		while (!feof($fp) && ($p = ftell($fp)) <= $end) {

			if ($p + $buffer > $end) {

				// In case we're only outputtin a chunk, make sure we don't
				// read past the length
				$buffer = $end - $p + 1;
			}
			echo fread($fp, $buffer);
			flush(); // Free up memory. Otherwise large files will trigger PHP's memory limit.
		}
		fclose($fp);
	}

	/**
	 * guess the mime type of a file using a which cli call
	 * @param string $fileName
	 *
	 * @return string mime type
	 */
	public static function guessMime($fileName)
	{
		$call = new CliCall('file', array('-b', '--mime', $fileName));
		$call->execute();
		if ($call->exitStatus == 0) {
			$guess = trim($call->stdOut);
			if (strpos($guess, ';') !== false) {
				$guess = trim(substr($guess, 0, strpos($guess, ';')));
			}
			return $guess;
		} else {
			trigger_error('could not guess mime for ' . $fileName . ' maybe the file cmd is not preset');
			return 'application/octet-stream';
		}
	}

	/**
	 * add paths to include
	 * @param string[] $paths the paths you want to add
	 */
	public static function addIncludePaths($paths)
	{
		$oldPaths = explode(PATH_SEPARATOR, get_include_path());
		ini_set('include_path', implode(PATH_SEPARATOR, array_unique(array_merge($oldPaths, $paths))));
	}

	/**
	 * get the server sth. like http://domain.com:8080
	 *
	 * @param boolean $requireSSL force ssl
	 * @param boolean $addCredentials be careful with this one - it adds the credentials of the active http request so that the return value will be sth. like http://myName:myPassword@host.com
	 *
	 * @return string sth. like http://host.com
	 */
	public static function getServerUrl($requireSSL = null, $addCredentials = false)
	{
		if ($addCredentials && isset($_SERVER["PHP_AUTH_USER"]) && isset($_SERVER["PHP_AUTH_PW"])) {
			$credentials = urlencode($_SERVER["PHP_AUTH_USER"]) . ':' . urlencode($_SERVER["PHP_AUTH_PW"]) . '@';
		} else {
			$credentials = '';
		}
		if (isset($_SERVER['HTTPS']) || $requireSSL || (isset($_SERVER['HTTPS']) && is_null($requireSSL))) {
			$serverUrl = 'https://';
			if ($_SERVER['SERVER_PORT'] == '443') {
				$port = '';
			} else {
				$port = ':' . $_SERVER['SERVER_PORT'];
			}
		} else {
			$serverUrl = 'http://';
			if (isset($_SERVER['SERVER_PORT'])) {
				if ($_SERVER['SERVER_PORT'] == '80') {
					$port = '';
				} else {
					$port = ':' . $_SERVER['SERVER_PORT'];
				}
			} else {
				$port = '80';
			}
		}
		if (isset($_SERVER['HTTP_HOST'])) {
			$host = $_SERVER['HTTP_HOST'];
		} else {
			$host = 'localhost';
		}
		return $serverUrl . $credentials . $host; //. '-' .  $port;
	}

	/**
	 * follow ini setting log_errors and add a string to the php error_log
	 *
	 * @param string $logString
	 */
	public static function appendToPhpErrorLog($logString)
	{

		if (ini_get('log_errors')) {
			$logFile = ini_get('error_log');
			if (file_exists($logFile) && is_writable($logFile)) {
				$fp = fopen($logFile, 'a+');
				fwrite($fp, $logString);
			}
		}
	}

}
