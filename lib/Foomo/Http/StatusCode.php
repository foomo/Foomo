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
 * http status code with corresponding status message
 * (inspired by the recessframework)
 *
 * @link www.foomo.org
 * @link http://www.recessframework.org/
 * @license www.gnu.org/licenses/lgpl.txt
 * @author frederik <frederik@bestbytes.de>
 */
class StatusCode {

	//---------------------------------------------------------------------------------------------
	// ~ 1xx Informational
	//---------------------------------------------------------------------------------------------
	const CODE_100_CONTINUE = 100;
	const CODE_101_SWITCHING_PROTOCOLS = 101;

	//---------------------------------------------------------------------------------------------
	// ~ 2xx Success
	//---------------------------------------------------------------------------------------------
	const CODE_200_OK = 200;
	const CODE_201_CREATED = 201;
	const CODE_202_ACCEPTED = 202;
	const CODE_203_NONAUTHORITATIVE_INFORMATION = 203;
	const CODE_204_NO_CONTENT = 204;
	const CODE_205_RESET_CONTENT = 205;
	const CODE_206_PARTIAL_CONTENT = 206;

	//---------------------------------------------------------------------------------------------
	// ~ 3xx Redirection
	//---------------------------------------------------------------------------------------------
	const CODE_300_MULTIPLE_CHOICES = 300;
	const CODE_301_MOVED_PERMANENTLY = 301;
	const CODE_302_FOUND = 302;
	const CODE_303_SEE_OTHER = 303;
	const CODE_304_NOT_MODIFIED = 304;
	const CODE_305_USE_PROXY = 305;
	const CODE_306_UNUSED= 306;
	const CODE_307_TEMPORARY_REDIRECT = 307;

	//---------------------------------------------------------------------------------------------
	// ~ 4xx Client Error
	//---------------------------------------------------------------------------------------------
	const CODE_400_BAD_REQUEST = 400;
	const CODE_401_UNAUTHORIZED  = 401;
	const CODE_402_PAYMENT_REQUIRED = 402;
	const CODE_403_FORBIDDEN = 403;
	const CODE_404_NOT_FOUND = 404;
	const CODE_405_METHOD_NOT_ALLOWED = 405;
	const CODE_406_NOT_ACCEPTABLE = 406;
	const CODE_407_PROXY_AUTHENTICATION_REQUIRED = 407;
	const CODE_408_REQUEST_TIMEOUT = 408;
	const CODE_409_CONFLICT = 409;
	const CODE_410_GONE = 410;
	const CODE_411_LENGTH_REQUIRED = 411;
	const CODE_412_PRECONDITION_FAILED = 412;
	const CODE_413_REQUEST_ENTITY_TOO_LARGE = 413;
	const CODE_414_REQUEST_URI_TOO_LONG = 414;
	const CODE_415_UNSUPPORTED_MEDIA_TYPE = 415;
	const CODE_416_REQUESTED_RANGE_NOT_SATISFIABLE = 416;
	const CODE_417_EXPECTATION_FAILED = 417;

	//---------------------------------------------------------------------------------------------
	// ~ 5xx Server Error
	//---------------------------------------------------------------------------------------------
	const CODE_500_INTERNAL_SERVER_ERROR = 500;
	const CODE_501_NOT_IMPLEMENTED = 501;
	const CODE_502_BAD_GATEWAY = 502;
	const CODE_503_SERVICE_UNAVAILABLE = 503;
	const CODE_504_GATEWAY_TIMEOUT = 504;
	const CODE_505_VERSION_NOT_SUPPORTED = 505;



	//---------------------------------------------------------------------------------------------
	// ~ Private static functions
	//---------------------------------------------------------------------------------------------

	private static $messages = array(

		// 1xx Informational
		100 => 'Continue',
		101 => 'Switching Protocols',

		// 2xx Success
		200 => 'OK',
		201 => 'Created',
		202 => 'Accepted',
		203 => 'Non-Authoritative Information',
		204 => 'No Content',
		205 => 'Reset Content',
		206 => 'Partial Content',

		// 3xx Redirection
		300 => 'Multiple Choices',
		301 => 'Moved Permanently',
		302 => 'Found',
		303 => 'See Other',
		304 => 'Not Modified',
		305 => 'Use Proxy',
		306 => 'Switch Proxy', //unused
		307 => 'Temporary Redirect',

		// 4xx Client Error
		400 => 'Bad Request',
		401 => 'Unauthorized',
		402 => 'Payment Required',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		406 => 'Not Acceptable',
		407 => 'Proxy Authentication Required',
		408 => 'Request Timeout',
		409 => 'Conflict',
		410 => 'Gone',
		411 => 'Length Required',
		412 => 'Precondition Failed',
		413 => 'Request Entity Too Large',
		414 => 'Request-URI Too Long',
		415 => 'Unsupported Media Type',
		416 => 'Requested Range Not Satisfiable',
		417 => 'Expectation Failed',

		// 5xx Server Error
		500 => 'Internal Server Error',
		501 => 'Not Implemented',
		502 => 'Bad Gateway',
		503 => 'Service Unavailable',
		504 => 'Gateway Timeout',
		505 => 'HTTP Version Not Supported'
	);



	//---------------------------------------------------------------------------------------------
	// ~ Public static functions
	//---------------------------------------------------------------------------------------------

	/**
	 * @return string
	 */
	public static function getHttpProtocolStandard()
	{
		return 'HTTP/1.1';
	}

	/**
	 * set a header for the given status code
	 *
	 * @param $code
	 */
	public static function setHeader($code)
	{
		$header = array(
			self::getHttpProtocolStandard(),
			$code,
			self::getMessage($code)
		);
		header(implode(' ', $header));
	}

	/**
	 * returns the message for a given status code
	 *
	 * @param $code
	 *
	 * @return mixed
	 */
	public static function getMessage($code)
	{
		return self::$messages[$code];
	}

	/**
	 * checks if the given code is an error
	 *
	 * @param $code
	 *
	 * @return bool
	 */
	public static function isError($code)
	{
		return is_numeric($code) && $code >= self::CODE_BAD_REQUEST;
	}

	/**
	 * checks if this status code allows a HTML body
	 *
	 * @param $code
	 *
	 * @return bool
	 */
	public static function canHaveBody($code)
	{
		return
			// True if not in 100s
			($code < self::CODE_CONTINUE || $code >= self::CODE_OK)
			&& // and not 204 NO CONTENT
			$code != self::CODE_NO_CONTENT
			&& // and not 304 NOT MODIFIED
			$code != self::CODE_NOT_MODIFIED;
	}
}
