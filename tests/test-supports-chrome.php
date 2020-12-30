<?php

namespace Mantle\Browser_Testing\Tests;

use Mantle\Browser_Testing\Chrome\Supports_Chrome;
use PHPUnit\Framework\TestCase;

class Test_Supports_Chrome extends TestCase {

	use Supports_Chrome;

	public function test_it_can_run_chrome_process() {
		$process = static::build_chrome_process();

		$process->start();

		// Wait for the process to start up, and output any issues
		sleep( 2 );

		$process->stop();

		$this->assertStringContainsString( 'Starting ChromeDriver', $process->getOutput() );
		$this->assertSame( '', $process->getErrorOutput() );
	}
}
