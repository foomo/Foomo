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

namespace Foomo\Cache;

/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
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

