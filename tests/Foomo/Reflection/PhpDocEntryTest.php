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

namespace Foomo\Reflection;

/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
class PhpDocEntryTest extends \PHPUnit_Framework_TestCase {
	const MOCK_CLASS_NAME = 'Foomo\\Reflection\\MockClass';
	public function testClassProps()
	{
		$classRefl = new \ReflectionClass(self::MOCK_CLASS_NAME);
		$classDoc = new PhpDocEntry($classRefl->getDocComment());

		$seppRead = $this->getPropDocs('seppRead', $classDoc);
		$this->assertTrue($seppRead->read, 'sepp read must be readable');
		$this->assertFalse($seppRead->write, 'sepp read must not be writable');

		$seppWrite = $this->getPropDocs('seppWrite', $classDoc);
		$this->assertFalse($seppWrite->read, 'seppWrite must not be readable');
		$this->assertTrue($seppWrite->write, 'seppWrite must be writable');

		$seppReadWrite = $this->getPropDocs('seppReadWrite', $classDoc);
		$this->assertTrue($seppReadWrite->write && $seppReadWrite->read, 'must be readable and writable');

	}

	public function testParameters()
	{
		$docEntry = $this->getTestMethodDocEntry();
		$argBar = $this->getArg('bar', $docEntry);
		$argFooBar = $this->getArg('fooBar', $docEntry);

		$this->assertEquals($argBar->name, 'bar');
		$this->assertEquals($argBar->type, 'string');
		$this->assertEquals($argBar->comment, 'bar bar bar');

		$this->assertEquals($argFooBar->name, 'fooBar');
		$this->assertEquals($argFooBar->type, 'array');
		$this->assertEquals($argFooBar->comment, 'foo bar comment');
	}
	/**
	 * test method doc entry
	 *
	 * @return PhpDocEntry
	 */
	private function getTestMethodDocEntry()
	{
		$methodRefl = new \ReflectionMethod(self::MOCK_CLASS_NAME, 'foo');
		return new PhpDocEntry($methodRefl->getDocComment());
	}
	public function testMiscMethodDocs()
	{

		$docEntry = $this->getTestMethodDocEntry();
		$this->assertEquals('ignore', $docEntry->serviceGen);
		$this->assertEquals('ignore', $docEntry->wsdlGen);
		$this->assertEquals('somewhere else', $docEntry->see);
		$this->assertEquals('string', $docEntry->return->type);
		$this->assertEquals('well it returns a poem', $docEntry->return->comment);
		$this->assertEquals('MyMessage', $docEntry->serviceMessage[0]->type);
		$this->assertEquals('jan', $docEntry->author);
	}

	public function testClassProp()
	{
		$refl = new \ReflectionProperty(self::MOCK_CLASS_NAME, 'foo');
		$docEntry = new PhpDocEntry($refl->getDocComment());
		$this->assertEquals($docEntry->comment, 'foo prop');
		$this->assertEquals($docEntry->var->type, self::MOCK_CLASS_NAME);
	}
	public function testResolveType()
	{
		$entry = new PhpDocEntry(null, __NAMESPACE__);
		$this->assertEquals(__NAMESPACE__ . '\\PhpDocEntryTest', $entry->resolveType(__CLASS__), __NAMESPACE__ . '\\PhpDocEntryTest');
		$this->assertEquals(__NAMESPACE__ . '\\PhpDocEntryTest[]', $res = $entry->resolveType(__CLASS__ .'[]'), __NAMESPACE__ . '\\PhpDocEntryTest[] != ' . $res);
		$this->assertEquals('Exception', $entry->resolveType('\\Exception'), 'Exception');
		$this->assertEquals('Exception[]', $entry->resolveType('\\Exception[]'), 'Exception[]');
		$this->assertEquals('Foomo\\Reflection\\MockClass',	$entry->resolveType('Foomo\\Reflection\\MockClass'), 'Foomo\\Reflection\\MockClass');
	}
	/**
	 * @param type $name
	 * @param PhpDocEntry $docEntry
	 * @return PhpDocArg
	 */
	private function getArg($name, PhpDocEntry $docEntry)
	{
		foreach($docEntry->parameters as $parameter) {

			if($parameter->name == $name) {
				return $parameter;
			}
		}
		$this->fail('paramater named "'.$name.'" could not be found');
	}
	/**
	 * get class property docs
	 *
	 * @param string $name of property
	 * @param PhpDocEntry $docEntry
	 * @return PhpDocProperty
	 */
	private function getPropDocs($name, PhpDocEntry $docEntry)
	{
		foreach($docEntry->properties as $prop) {
			if($prop->name == $name) {
				return $prop;
			}
		}
		$this->fail('could not find property "'.$name.'" in doc entry');
	}
}