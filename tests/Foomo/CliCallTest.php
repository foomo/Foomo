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
 * test the domain config
 *
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
class CliCallTest extends \PHPUnit_Framework_TestCase {
	public function testSimpleCommand()
	{
		$test = 'hallo test';
		$call = new CliCall('echo', array($test));
		$call->execute();
		$this->assertEquals(0, $call->exitStatus);
		$this->assertEquals($test, $call->stdOut);
		$this->assertEquals('', $call->stdErr);
	}

	public function testSetEnvironment1()
	{
		$test = 'abcdef';
		$cmdFile = tempnam(Config::getTempDir(), 'testCmd_');
		file_put_contents($cmdFile, 'echo $MY_VARIABLE' . PHP_EOL);
		$call = new CliCall('bash', array($cmdFile), array('MY_VARIABLE' => $test));
		$call->execute();
		unlink($cmdFile);
		$this->assertEquals(0, $call->exitStatus);
		$this->assertEquals($test, $call->stdOut);
		$this->assertEquals('', $call->stdErr);
	}

	public function testSetEnvironment2()
	{
		$test1 = 'abcdef';
		$test2 = '012345';
		$cmdFile = tempnam(Config::getTempDir(), 'testCmd_');
		file_put_contents($cmdFile, 'echo $MY_VARIABLE1 - $MY_VARIABLE2' . PHP_EOL);
		$call = new CliCall('bash', array($cmdFile), array('MY_VARIABLE1' => $test1, 'MY_VARIABLE2' => $test2));
		$call->execute();
		unlink($cmdFile);
		$this->assertEquals(0, $call->exitStatus);
		$this->assertEquals("$test1 - $test2", $call->stdOut);
		$this->assertEquals('', $call->stdErr);
	}

	public function testEscapedShellArguments()
	{
		$test = "a'bc\ndef\\";
		$call = new CliCall('echo', array($test));
		$call->execute();
		$this->assertEquals(0, $call->exitStatus);
		$this->assertEquals($test, $call->stdOut);
		$this->assertEquals('', $call->stdErr);
	}

	public function testEscapedSetEnvironment1()
	{
		$test = 'a\'bcdef\\';
		$cmdFile = tempnam(Config::getTempDir(), 'testCmd_');
		file_put_contents($cmdFile, 'echo $MY_VARIABLE' . PHP_EOL);
		$call = new CliCall('bash', array($cmdFile), array('MY_VARIABLE' => $test));
		$call->execute();
		unlink($cmdFile);
		$this->assertEquals(0, $call->exitStatus);
		$this->assertEquals($test, $call->stdOut, "'$test' vs. '{$call->stdOut}'");
		$this->assertEquals('', $call->stdErr);
	}

	public function testSetEnvironment3()
	{
		$test = "abcdef";
		$cmdFile = tempnam(Config::getTempDir(), 'testCmd_');
		file_put_contents($cmdFile, 'set|grep MY_VARIABLE' . PHP_EOL);
		$call = new CliCall('bash', array($cmdFile), array('MY_VARIABLE' => $test));
		$call->execute();
		unlink($cmdFile);
		$this->assertEquals(0, $call->exitStatus);
		$this->assertEquals("MY_VARIABLE=$test", $call->stdOut);
		$this->assertEquals('', $call->stdErr);
	}

	public function testEscapedSetEnvironment3()
	{
		$test = 'a\'bc\ndef\\';
		$cmdFile = tempnam(Config::getTempDir(), 'testCmd_');
		file_put_contents($cmdFile, 'set|grep MY_VARIABLE' . PHP_EOL);
		$call = new CliCall('bash', array($cmdFile), array('MY_VARIABLE' => $test));
		$call->execute();
		unlink($cmdFile);
		$this->assertEquals(0, $call->exitStatus);
		$this->assertEquals("MY_VARIABLE=" . escapeshellarg($test), $call->stdOut);
		$this->assertEquals('', $call->stdErr);
	}

	public function testExitCode()
	{
		$test = 7;
		$cmdFile = tempnam(Config::getTempDir(), 'testCmd_');
		file_put_contents($cmdFile, 'exit $MY_VARIABLE' . PHP_EOL);
		$call = new CliCall('bash', array($cmdFile), array('MY_VARIABLE' => $test));
		$call->execute();
		unlink($cmdFile);
		$this->assertEquals($test, $call->exitStatus);
		$this->assertEquals('', $call->stdOut);
		$this->assertEquals('', $call->stdErr);
	}
	public function testCallbackStream()
	{
		$cmdFile = tempnam(Config::getTempDir(), 'testCmd_');
		$script = 'php -r "ini_set(\"display_errors\", \"Off\");foreach(array(1,2,3) as \$num) {sleep(1); echo \$num . PHP_EOL;};trigger_error(\"autsch\", E_USER_ERROR);"';
		file_put_contents($cmdFile, $script);
		$expectedStdOut = implode(array(1,2,3), PHP_EOL) . PHP_EOL;
		$stdOut = '';
		$call = CliCall::create('bash', array($cmdFile))
			->setStdOutStreamCallback(function($stream) use (&$stdOut) {
				$stdOut .= stream_get_contents($stream);
			})
			->execute()
		;
		unlink($cmdFile);
		$this->assertEquals($expectedStdOut, $stdOut);
		$this->assertContains('autsch', $call->stdErr);
		$this->assertEquals(255, $call->exitStatus);
	}

	private static function getStreamer($numberOfByteToStdOut, $numberOfBytesToStdError)
	{
		$call = CliCall::create('php', array(__DIR__ . DIRECTORY_SEPARATOR . 'CliCall' . DIRECTORY_SEPARATOR . 'streamer.php', $numberOfByteToStdOut, $numberOfBytesToStdError));
		return $call;
	}
	public function testStreams()
	{
		$cases = array(
			'noStdOutBigStdErr' => array(0, $big = 512*1024),
			'noStdErrBigStdOut' => array($big, 0),
			'allBig' => array($big, $big)
		);
		ini_set('html_errors', 'Off');
		foreach($cases as $name => $limits) {
			$call = self::getStreamer($numStdOut = $limits[0], $numStdErr = $limits[1]);
			$call->execute();
			$this->assertEquals($numStdErr, strlen($call->stdErr), 'wrong stdErr for ' . $name);
			$this->assertEquals($numStdOut, strlen($call->stdOut), 'wrong stdOut for ' . $name);
		}
	}

}