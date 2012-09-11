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
		\Foomo\Jobs\Runner::runAJob(Mock\SleeperJob::create());
		$status = \Foomo\Jobs\Utils::getStatus(Mock\SleeperJob::create());
		$this->assertEquals(JobStatus::STATUS_NOT_RUNNING, $status->status);
	}

	public function testSleeperJob() {
		self::callAsync('SleeperJob');
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
		self::callAsync('DierJob');
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
		self::callAsync('ExiterJob');
		sleep(2);
		$status = \Foomo\Jobs\Utils::getStatus(Mock\ExiterJob::create());
		$this->assertEquals(JobStatus::STATUS_NOT_RUNNING, $status->status, 'we should not be running now');
		$this->assertNotEquals(getmypid(), $status->pid, 'pid should differ');
		$this->assertFalse($status->isLocked, 'should not be locked');
	}

	public function testDieWhileWorkingJob() {
		self::callAsync('DieWhileWorkingJob');
		sleep(2);
		$status = \Foomo\Jobs\Utils::getStatus(Mock\DieWhileWorkingJob::create());
		$this->assertNotEquals(getmypid(), $status->pid, 'pid should differ');
		$this->assertFalse($status->isLocked, 'should not be locked');
		$this->assertEquals(JobStatus::STATUS_NOT_RUNNING, $status->status, 'we should not be running now');
		$this->assertEquals(JobStatus::ERROR_DIED, $status->errorCode);
		
	}
	
	
	public function testConcurrency() {
		self::callAsync('SleeperJob');
		sleep(1);
		self::callAsync('SleeperJob');
		$status = \Foomo\Jobs\Utils::getStatus(Mock\SleeperJob::create());
		$this->assertEquals(JobStatus::ERROR_ATTEMPTED_CONCURRENT_RUN, $status->errorCode);
		$status = \Foomo\Jobs\Utils::getStatus(Mock\SleeperJob::create());
	}
	
	private function exposeTestScript() {
		$file = __DIR__ . DIRECTORY_SEPARATOR . 'runJob.php';
		symlink($file, $this->getTempJobRunnerFile());
	}

	private function hideTestScript() {
		unlink($this->getTempJobRunnerFile());
	}
	
	private function getTempJobRunnerFile()
	{
		return \Foomo\Module::getHtdocsVarDir() . DIRECTORY_SEPARATOR . 'runJob.php';
	}
	
	private function getTempRunnerEndpoint($job)
	{
		return \Foomo\Utils::getServerUrl() . \Foomo\Module::getHtdocsVarPath() . '/runJob.php?job=' . urlencode($job);
	}

	private function callAsync($job) {
		$ch = \curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->getTempRunnerEndpoint($job));
		curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 1);
		curl_exec($ch);
		curl_close($ch);
	}

}