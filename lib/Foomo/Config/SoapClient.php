<?php

/*
 * bestbytes-copyright-placeholder
 */

namespace Foomo\Config;

use Foomo\Config\AbstractConfig;

/**
 * configuration for a soap client
 * @todo move to Foomo.Services
 */
class SoapClient extends AbstractConfig {
	const NAME = 'Foomo.Services.soapClient';
	/**
	 * which soap version SOAP_1_1 | SOAP_1_2
	 *
	 * @var string
	 */
	public $soapVersion;
	/**
	 * URL where to get the wsdl
	 *
	 * @var string
	 */
	public $wsdlUrl;
	/**
	 * URL alternate endpoint than described in the wsdl
	 *
	 * @var string
	 */
	public $endPointUrl;
	/**
	 * need a proxy? Use format http://user:password@hostname:port/
	 *
	 * @var string
	 */
	public $proxyUrl;
	/**
	 * how to map remote objects to local types
	 * 
	 *   array('RemoteType' => 'LocalType', ...)
	 *
	 * @var array
	 */
	public $classMap = array('SomeRemoteType' => 'SomeLocalType', 'SomeOtherRemoteType' => 'SomeOtherLocalType');
	/**
	 * user agent
	 *
	 * @var string
	 */
	public $userAgent;
	/**
	 * enables the use of SapClient->__getLast.. methods
	 *
	 * @var boolean
	 */
	public $trace = true;
	/**
	 * exceptions are thrown as SoapFault
	 *
	 * @var boolean
	 */
	public $throwsSoapFault = true;
	public function __construct($createDefault = false)
	{
		if ($createDefault) {
			if (\Foomo\Config::getMode() == \Foomo\Config::MODE_PRODUCTION) {
				$this->trace = false;
			}
		}
	}

	public function setValue($value)
	{
		parent::setValue($value);
		$this->throwsSoapFault = (boolean) $this->throwsSoapFault;
	}

	/**
	 * get a configured soap client
	 * 
	 * @return SoapClient
	 */
	public function getSoapClient()
	{
		$options = array();
		// soapVersion
		if (isset($this->soapVersion)) {
			$options['soap_version'] = $this->soapVersion;
		}
		// wsdl
		$wsdl = $this->wsdlUrl;

		// basic auth
		$endpointUrl = parse_url($this->endPointUrl);
		if (!empty($endpointUrl['user']) && !empty($endpointUrl['pass'])) {
			$options['login'] = $endpointUrl['user'];
			$options['password'] = $endpointUrl['pass'];
		}

		// proxy
		if (isset($this->proxyUrl)) {
			$proxyUrl = parse_url($this->proxyUrl);
			if (!empty($proxyUrl['user']) && !empty($proxyUrl['pass'])) {
				$options['proxy_login'] = $proxyUrl['user'];
				$options['proxy_password'] = $proxyUrl['pass'];
			}
			$options['proxy_host'] = $proxyUrl['host'];
			if (!empty($proxyUrl['port'])) {
				$options['proxy_port'] = $proxyUrl['port'];
			}
		}

		// class map
		if (is_array($this->classMap)) {
			$options['classmap'] = $this->classMap;
		}

		// user agent
		if (isset($this->userAgent)) {
			$options['user_agent'] = $this->userAgent;
		}

		// trace
		$options['trace'] = (boolean) $this->trace;
		// exceptions
		$options['exceptions'] = $this->throwsSoapFault;

		return new \SoapClient($wsdl, $options);
	}

}