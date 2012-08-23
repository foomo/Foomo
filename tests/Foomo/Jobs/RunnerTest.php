<?php

namespace Foomo\Jobs;

class RunnerTest extends \PHPUnit_Framework_TestCase {

	public function setUp() {
		$this->exposeTestScript();
		parent::setUp();
	}

	public function tearDown() {
		$this->hideTestScript();
		parent::tearDown();
	}

	public function testRun() {
		$jobs = Utils::collectJobs();
		$executionSecret = Utils::getExecutionSecret();
		\Foomo\Jobs\Runner::runJob(Mock\SleeperJob::create()->getSecretId($executionSecret));
		$status = \Foomo\Jobs\Utils::getStatus(Mock\SleeperJob::create());
		$this->assertEquals(JobStatus::STATUS_NOT_RUNNING, $status->status);
	}

	public function testSleeperJob() {
		self::callAsync(\Foomo\Utils::getServerUrl() . '/foomo/runJob.php?job=SleeperJob');
		sleep(1);
		$status = \Foomo\Jobs\Utils::getStatus(Mock\SleeperJob::create());
		$this->assertEquals(JobStatus::STATUS_RUNNING, $status->status, 'we should be running now');
		$this->assertNotEquals(getmypid(), $status->pid, 'pid should differ');
		$this->assertTrue($status->isLocked, 'should be locked');
		sleep(5);
		$status = \Foomo\Jobs\Utils::getStatus(Mock\SleeperJob::create());
		$this->assertEquals(JobStatus::STATUS_NOT_RUNNING, $status->status, 'we should not be running now');
		$this->assertNotEquals(getmypid(), $status->pid, 'pid should differ');
		$this->assertFalse($status->isLocked, 'should not be locked');
	}

	public function testDierJob() {
		self::callAsync(\Foomo\Utils::getServerUrl() . '/foomo/runJob.php?job=DierJob');
		sleep(1);
		$status = \Foomo\Jobs\Utils::getStatus(Mock\DierJob::create());
		$this->assertEquals(JobStatus::STATUS_RUNNING, $status->status, 'we should be running now');
		$this->assertNotEquals(getmypid(), $status->pid, 'pid should differ');
		$this->assertTrue($status->isLocked, 'should be locked');

		sleep(5);
		$status = \Foomo\Jobs\Utils::getStatus(Mock\DierJob::create());
		$this->assertEquals(JobStatus::STATUS_NOT_RUNNING, $status->status, 'we should not be running now');
		$this->assertNotEquals(getmypid(), $status->pid, 'pid should differ');
		$this->assertFalse($status->isLocked, 'should not be locked');
		$this->assertEquals(JobStatus::ERROR_DIED, $status->errorCode);
	}

	
	public function testExiterJob() {
		self::callAsync(\Foomo\Utils::getServerUrl() . '/foomo/runJob.php?job=ExiterJob');
		sleep(2);
		$status = \Foomo\Jobs\Utils::getStatus(Mock\ExiterJob::create());
		$this->assertEquals(JobStatus::STATUS_NOT_RUNNING, $status->status, 'we should not be running now');
		$this->assertNotEquals(getmypid(), $status->pid, 'pid should differ');
		$this->assertFalse($status->isLocked, 'should not be locked');
	}

	public function testDieInSleepJob() {
		self::callAsync(\Foomo\Utils::getServerUrl() . '/foomo/runJob.php?job=DieInSleepJob');
		sleep(4);
		$status = \Foomo\Jobs\Utils::getStatus(Mock\DieInSlepJob::create());
		$this->assertNotEquals(getmypid(), $status->pid, 'pid should differ');
		$this->assertFalse($status->isLocked, 'should not be locked');
		$this->assertEquals(JobStatus::STATUS_NOT_RUNNING, $status->status, 'we should not be running now');
		
	}
	
	
	public function testConcurrency() {
		self::callAsync(\Foomo\Utils::getServerUrl() . '/foomo/runJob.php?job=SleeperJob');
		sleep(1);
		self::callAsync(\Foomo\Utils::getServerUrl() . '/foomo/runJob.php?job=SleeperJob');
		$status = \Foomo\Jobs\Utils::getStatus(Mock\SleeperJob::create());
		$this->assertEquals(JobStatus::ERROR_ATTEMPTED_CONCURRENT_RUN, $status->errorCode);
		$status = \Foomo\Jobs\Utils::getStatus(Mock\SleeperJob::create());
		var_dump($status);
		
	}
	
	
	
	
	private function exposeTestScript() {
		$file = __DIR__ . DIRECTORY_SEPARATOR . 'runJob.php';
		symlink($file, \Foomo\Config::getHtdocsDir(\Foomo\Module::NAME) . DIRECTORY_SEPARATOR . 'runJob.php');
	}

	private function hideTestScript() {
		unlink(\Foomo\Config::getHtdocsDir(\Foomo\Module::NAME) . DIRECTORY_SEPARATOR . '/runJob.php');
	}

	private function callAsync($url) {
		$ch = \curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 1);
		curl_exec($ch);
		curl_close($ch);
	}

}