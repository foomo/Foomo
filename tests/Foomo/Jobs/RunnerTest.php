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
		\Foomo\Jobs\Runner::runAJob(Mock\SleeperJob::create());
		$status = \Foomo\Jobs\Utils::getStatus(Mock\SleeperJob::create());
		$this->assertEquals(JobStatus::STATUS_NOT_RUNNING, $status->status);
	}
	
	public function testExecutionRuleValidation() {

		try{
			Mock\SleeperJob::create()->executionRule('* * * * *');
		} catch (\Exception $e) {
			
		}
		
		try{
			Mock\SleeperJob::create()->executionRule('61 * * * *');
			$this->assertTrue('false', 'wrong param should have been detected');
		} catch (\Exception $e) {
			
			$this->assertTrue($e instanceof \InvalidArgumentException, 'wrong type of exception');
		}
		
		try{
			Mock\SleeperJob::create()->executionRule('59,62 * * * *');
			$this->assertTrue('false', 'wrong param should have been detected');
		} catch (\Exception $e) {
			
			$this->assertTrue($e instanceof \InvalidArgumentException, 'wrong type of exception');
		}

		
		try{
			Mock\SleeperJob::create()->executionRule('* 25 * * *');
			$this->assertTrue('false', 'wrong param should have been detected');
		} catch (\Exception $e) {
			
			$this->assertTrue($e instanceof \InvalidArgumentException, 'wrong type of exception');
		}


		try{
			Mock\SleeperJob::create()->executionRule('* * 32 * *');
			$this->assertTrue('false', 'wrong param should have been detected');
		} catch (\Exception $e) {
			
			$this->assertTrue($e instanceof \InvalidArgumentException, 'wrong type of exception');
		}
		
		try{
			Mock\SleeperJob::create()->executionRule('* * * 13 *');
			$this->assertTrue('false', 'wrong param should have been detected');
		} catch (\Exception $e) {
			
			$this->assertTrue($e instanceof \InvalidArgumentException, 'wrong type of exception');
		}

		try{
			Mock\SleeperJob::create()->executionRule('* * * * 7');
			$this->assertTrue('false', 'wrong param should have been detected');
		} catch (\Exception $e) {
			
			$this->assertTrue($e instanceof \InvalidArgumentException, 'wrong type of exception');
		}
		
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
		sleep(1);
		$status = \Foomo\Jobs\Utils::getStatus(Mock\DieWhileWorkingJob::create());
		$json = json_encode($status, JSON_PRETTY_PRINT) . " time: " . time();
		$this->assertNotEquals(getmypid(), $status->pid, 'pid should differ');
		$this->assertFalse($status->isLocked, 'should not be locked ' . $json);
		$this->assertEquals(JobStatus::STATUS_NOT_RUNNING, $status->status, 'we should not be running now ' . $json);
		$this->assertEquals(JobStatus::ERROR_DIED, $status->errorCode, $json);
		
	}
	public function testDieWithExceptionJob() {
		self::callAsync('DieWithExceptionJob');
		sleep(1);
		$status = \Foomo\Jobs\Utils::getStatus(Mock\DieWithExceptionJob::create());
		$json = json_encode($status, JSON_PRETTY_PRINT) . " time: " . time();
		$this->assertFalse($status->isLocked, 'should not be locked ' . $json);
		$this->assertEquals(JobStatus::STATUS_NOT_RUNNING, $status->status, 'we should not be running now ' . $json);
		$this->assertEquals(JobStatus::ERROR_DIED, $status->errorCode, $json);
		$this->assertEquals(__NAMESPACE__ . "\\Mock\\DieWithExceptionJob::run", $status->errorMessage, $json);


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