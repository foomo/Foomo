<?php
namespace Foomo\Cache;
abstract class AbstractBaseTest extends \PHPUnit_Framework_TestCase{

	public function setUp() {
		$domainConfig = \Foomo\Config::getConf(\Foomo\Module::NAME, \Foomo\Cache\Test\DomainConfig::NAME);
		$fastPersistorConf = $domainConfig->fastPersistors['memcached'];
		$queryablePersistorConf = $domainConfig->queryablePersistors['pdo'];
		$fastPersistor = \Foomo\Cache\Manager::getPersistorFromConf($fastPersistorConf, false);
		$pdoPersistor = \Foomo\Cache\Manager::getPersistorFromConf($queryablePersistorConf, true);
		Manager::initialize($pdoPersistor, $fastPersistor);
		\ob_start();
		Manager::reset(null, true, false);
		\ob_end_clean();
	}

}
