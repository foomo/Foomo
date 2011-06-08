<?php

namespace Foomo\Cache;

class DependencyModelTest extends \PHPUnit_Framework_TestCase {

	/**
	 *
	 * @var DependencyModel
	 */
	private $model;

	public function setUp() {
		$this->model = DependencyModel::getInstance();
	}

	public function testGetDependencies() {
		$resourceName = 'Foomo\Cache\MockObjects\SampleResources->getHoroscopeData';
		$dependencies = $this->model->getDependencies($resourceName);

		$this->assertEquals(
			array('Foomo\\Cache\\MockObjects\\SampleResources->noticeMEEEEEEE','Foomo\\Cache\\MockObjects\\SampleResources->renderHoroscope','Foomo\\Cache\\MockObjects\\SampleResources->renderHoroscope3D'),
			$dependencies
		);
	}

	public function testGetDirectory() {
		$dir = $this->model->getDirectory();
		$this->assertNotNull($dir['Foomo\Cache\MockObjects\SampleResources->renderHoroscope']);
		$this->assertTrue(in_array('Foomo\Cache\MockObjects\SampleResources->getHoroscopeData', $dir['Foomo\Cache\MockObjects\SampleResources->renderHoroscope']->description->dependencies));
	}

	public function testRenderDependencyTree() {
		foreach ($this->model->getAvailableResources() as $resourceName) {
			$this->assertStringStartsWith('dependency tree for ' . $resourceName, $this->model->renderDependencyTree($resourceName));
		}
	}

	public function testGetDependencyTree() {

		$resourceName = 'Foomo\Cache\MockObjects\SampleResources->getHoroscopeData';
		$dependencyTree = $this->model->getDependencyTree($resourceName);
		$expectedTree = array (
		  'Foomo\\Cache\\MockObjects\\SampleResources->getHoroscopeData' =>
		  array (
			'Foomo\\Cache\\MockObjects\\SampleResources->noticeMEEEEEEE' =>
			array (
			),
			'Foomo\\Cache\\MockObjects\\SampleResources->renderHoroscope' =>
			array (
			  'Foomo\\Cache\\MockObjects\\SampleResources->sendHosroscopeRendering' =>
			  array (
				'Foomo\\Cache\\MockObjects\\SampleResources->checkHoroscopeReception' =>
				array (
				),
				'Foomo\\Cache\\MockObjects\\SampleResources->checkHoroscopeValid' =>
				array (
				),
			  ),
			),
			'Foomo\\Cache\\MockObjects\\SampleResources->renderHoroscope3D' =>
			array (
			),
		  ),
		);
		$this->assertEquals($expectedTree, $dependencyTree);
		
	}

	public function testGetDependencyList() {

		$resourceName = 'Foomo\Cache\MockObjects\SampleResources->getHoroscopeData';
		$dependencyList = $this->model->getDependencyList($resourceName);

		$expectedList = array (
		  0 => 'Foomo\\Cache\\MockObjects\\SampleResources->getHoroscopeData',
		  1 => 'Foomo\\Cache\\MockObjects\\SampleResources->noticeMEEEEEEE',
		  2 => 'Foomo\\Cache\\MockObjects\\SampleResources->renderHoroscope',
		  3 => 'Foomo\\Cache\\MockObjects\\SampleResources->sendHosroscopeRendering',
		  4 => 'Foomo\\Cache\\MockObjects\\SampleResources->checkHoroscopeReception',
		  5 => 'Foomo\\Cache\\MockObjects\\SampleResources->checkHoroscopeValid',
		  6 => 'Foomo\\Cache\\MockObjects\\SampleResources->renderHoroscope3D',
		);

		$this->assertEquals($expectedList, $dependencyList);
	}

}

