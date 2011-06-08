<?php

namespace Foomo\Http;

use Foomo\Utils;

/**
 * test the "browser cache"
 *
 */
class BrowserCacheTest extends \PHPUnit_Framework_TestCase {
	
	// helper functions
	
	/**
	 * where to put the mock endpoint file
	 *
	 * @return string
	 */
	private function getEndPointFilename()
	{
		return \Foomo\ROOT . '/htdocs/foomoBrowserCacheTestEndPoint.php'; 
	}
	/**
	 * get an endpoint
	 *
	 * @param string $action
	 * @param array $parameters
	 * @return string
	 */
	private function getEndPoint($action = 'default', $parameters = array())
	{
		$helper = new \Foomo\MVC\ControllerHelper();
		$helper->setBaseURI(Utils::getServerUrl() . \Foomo\ROOT_HTTP . '/foomoBrowserCacheTestEndPoint.php');
		return html_entity_decode($helper->renderAppLink('Foomo\\Http\\MockServer', $action, $parameters));
	}
	/**
	 * dirty hack to extract a given returned header
	 *
	 * @param string $reply
	 * @param string $headerName
	 * @return string
	 */
	private function extractHeaderFromReply($reply, $headerName)
	{
		foreach(explode(PHP_EOL, $reply) as $line) {
			$line = trim($line);
			$parts = explode($headerName . ':', $line);
			if(count($parts) == 2) {
				return trim($parts[1]);
			}
		}
	}
	/**
	 * curl request to the mock endpoint
	 *
	 * @param atring $action
	 * @param array $parameters
	 * @param array $headers
	 * 
	 * @return string
	 */
	private function request($action, $parameters, $headers = array())
	{
		$endPoint = $this->getEndPoint($action, $parameters);
		$ch = curl_init($endPoint);
		//curl_setopt ($ch, CURLOPT_HEADER, 1);
		// curl_setopt ($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_HEADER, true);//array('x-kacke:' => 'scheisse'));
		if(count($headers)>0) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		}
		//curl_setopt($ch, CURLOPT_HEADER, 'If-None-Match: sfdhjfdsiufdsjk-1fjskd');
		//curl_setopt ($ch, CURLOPT_POSTFIELDS, 'call=' . urlencode($data));
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_COOKIESESSION, true);
		curl_setopt($ch, CURLOPT_USERAGENT, 'unit test agent');
		//curl_setopt ($ch, CURLOPT_NOBODY, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		$result = curl_exec($ch);
		if($result === false) {
			trigger_error('a curl error occurred ' . curl_error($ch), E_USER_ERROR);
		}
		return $result;
	}	
		
	// set up and tear down
	
	
	/**
	 * set up a mock endpoint
	 *
	 */
	public function setUp()
	{
		if(file_exists($this->getEndPointFilename())) {
			trigger_error($this->getEndPointFilename() . ' exists can not run this test', E_USER_ERROR);
		}
		file_put_contents($this->getEndPointFilename(), file_get_contents(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'testEndPoint.php'));
	}
	/**
	 * clean up again
	 *
	 */
	public function tearDown()
	{
		unlink($this->getEndPointFilename());
	}
		
	private function getEnabled()
	{
		if(php_sapi_name() != 'cli') {
			return true;
		} else {
			return false;
		}
	}
	private function skipp()
	{
		self::markTestSkipped('can not run with sapi ' . php_sapi_name());
	}
	public function testGetCacheableReply()
	{
		if($this->getEnabled()) {
			$expectedLastMod = time();
			//echo file_get_contents();
			$reply = $this->request(
				'actionLastModified',
				array('lastModified' => $expectedLastMod),
				array()
			);
			// get the resource and its expiry and ETag and check them
			$lastMod = $this->extractHeaderFromReply($reply, 'Last-Modified');
			$ETag = $this->extractHeaderFromReply($reply, 'Etag');
			//echo '>>>>>>>>> $lastMod ' . $lastMod . ' "' . strtotime($lastMod, time()) . '" ('.date('Y-m-d H:i:s T', strtotime($lastMod)).') with ETag "' . $ETag . '"';
			$this->assertTrue(($expectedLastMod == strtotime($lastMod)), 'last modified is not ok ' . $expectedLastMod . ' != ' . strtotime($lastMod) );
			$this->assertTrue(MockServer::ETAG_LAST_MODIFIED_RESOURCE == $ETag, 'ETag is not ok');
		} else {
			$this->skipp();
		}
	}
	public function test304FailureLastMod()
	{
		if($this->getEnabled()) {
			$expectedLastMod = time();
			$lastMod = BrowserCache::getDate($expectedLastMod);
			$ETag = MockServer::ETAG_LAST_MODIFIED_RESOURCE;
			// try not to get a not 304 with relevant headers but a wrong last mod
			$reply = $this->request(
				'actionLastModified',
				array('lastModified' => $expectedLastMod-10),
				array('If-None-Match: ' . $ETag, 'If-Modified-Since: ' . $lastMod)
			);
			$this->assertContains('200 OK', $reply);
			$lastMod = $this->extractHeaderFromReply($reply, 'Last-Modified');
			$this->assertFalse(($expectedLastMod == strtotime($lastMod)), 'last modified should differ ' . ($expectedLastMod-10) . ' != ' . strtotime($lastMod) );
		} else {
			$this->skipp();
		}
	}
	public function test304FailureETag()
	{
		if($this->getEnabled()) {
			$expectedLastMod = time();
			$lastMod = BrowserCache::getDate($expectedLastMod);
			$ETag = MockServer::ETAG_LAST_MODIFIED_RESOURCE;
			// try not to get a not 304 with relevant headers but a wrong ETag
			$reply = $this->request(
				'actionLastModified',
				array('lastModified' => $expectedLastMod),
				array('If-None-Match: ' . strrev($ETag), 'If-Modified-Since: ' . $lastMod)
			);
			$this->assertContains('200 OK', $reply);
			$lastMod = $this->extractHeaderFromReply($reply, 'Last-Modified');
			$this->assertTrue(($expectedLastMod == strtotime($lastMod)), 'last modified is not ok ' . $expectedLastMod . ' != ' . strtotime($lastMod) );
		} else {
			$this->skipp();
		}
	}
	public function test304Success()
	{
		if($this->getEnabled()) {
			$expectedLastMod = time();
			$lastMod = BrowserCache::getDate($expectedLastMod);
			$ETag = MockServer::ETAG_LAST_MODIFIED_RESOURCE;
			// try to get a 304, with relevant headers

			$reply = $this->request(
				'actionLastModified',
				array('lastModified' => $expectedLastMod),
				array('If-None-Match: ' . $ETag, 'If-Modified-Since: ' . $lastMod)
			);
			$this->assertContains('304 Not Modified', $reply);
			$this->assertContains('Cache-Control: max-age=432000, private', $reply);
		} else {
			$this->skipp();
		}
		
	}
}