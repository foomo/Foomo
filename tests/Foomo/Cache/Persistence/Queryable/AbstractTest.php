<?php


namespace Foomo\Cache\Persistence\Queryable;

use Foomo\Cache\Proxy;

abstract class AbstractTest extends \PHPUnit_Framework_TestCase {
	const MOCK_CLASS_NAME = 'Foomo\Cache\MockObjects\SampleResources';
	/**
	 *
	 * @var \Foomo\Cache\Persistence\QueryablePersistorInterface
	 */
	protected $persistor;
	// abstract public function setUp();

	private function createResource($args = array('fooVal', 'barVal'))
	{
		return Proxy::getEmptyResource(self::MOCK_CLASS_NAME, 'test', $args);
	}
	function testSave()
	{
		$resource = $this->createResource();
		$this->persistor->save($resource);
		$cachedResource = $this->persistor->load($resource);
		$this->assertEquals($resource, $cachedResource);
	}
	function testDelete()
	{
		$resource = $this->createResource();
		$this->persistor->save($resource);
		$cachedResource = $this->persistor->load($resource);
		$this->assertEquals($resource, $cachedResource);
		$this->persistor->delete($resource);
		$cachedResource = $this->persistor->load($resource);
		$this->assertEquals(null, $cachedResource);
	}
	public function testFind()
	{
		foreach($fooArray = array('a', 'b') as $foo) {
			foreach($barArray = array(1,2,3,4,5) as $bar) {
				$resource = $this->createResource(array($foo, $bar));
				$this->persistor->save($resource);
			}
		}
		$resourceIterator = $this->persistor->find(self::MOCK_CLASS_NAME . '::test');
		$this->assertEquals(count($fooArray) * count($barArray), count($resourceIterator));

		$resourceIterator = $this->persistor->find(self::MOCK_CLASS_NAME . '::test', array('foo' => 'a'));
		$this->assertEquals(count($barArray), count($resourceIterator));

		foreach($resourceIterator as $resource) {
			$this->assertArrayHasKey('foo', $resource->properties);
			$this->assertArrayHasKey('bar', $resource->properties);
			$this->assertEquals('a', $resource->properties['foo']);
		}
	}
}