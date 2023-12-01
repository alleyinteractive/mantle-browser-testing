<?php

namespace Mantle\Browser_Testing\Tests;

use Mantle\Browser_Testing\Chrome\Chrome_Process;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Process\Process;

class Test_Chrome_Process extends TestCase {

	public function test_build_process_with_custom_driver() {
		$driver = __DIR__;

		$process = ( new Chrome_Process( $driver ) )->to_process();

		$this->assertInstanceOf( Process::class, $process );
		$this->assertStringContainsString( "$driver", $process->getCommandLine() );
	}

	public function test_build_process_for_windows() {
		try {
			( new Chrome_ProcessWindows() )->to_process();
		} catch ( RuntimeException $exception ) {
			$this->assertStringContainsString( 'chromedriver-win.exe', $exception->getMessage() );
		}
	}

	public function test_build_process_for_darwin_intel() {
		try {
			( new Chrome_ProcessDarwinIntel() )->to_process();
		} catch ( RuntimeException $exception ) {
			$this->assertStringContainsString( 'chromedriver-mac-intel', $exception->getMessage() );
		}
	}

	public function test_build_process_for_darwin_arm() {
		try {
			( new Chrome_ProcessDarwinArm() )->to_process();
		} catch ( RuntimeException $exception ) {
			$this->assertStringContainsString( 'chromedriver-mac-arm', $exception->getMessage() );
		}
	}

	public function test_build_process_for_linux() {
		try {
			( new Chrome_ProcessLinux() )->to_process();
		} catch ( RuntimeException $exception ) {
			$this->assertStringContainsString( 'chromedriver-linux', $exception->getMessage() );
		}
	}

	public function test_invalid_path() {
		$this->expectException( RuntimeException::class );
		$this->expectExceptionMessage( 'Invalid path to Chromedriver [/not/a/valid/path]. Make sure to install the Chromedriver first by running the ./bin/mantle browser-testing:chrome-driver command.' );

		( new Chrome_Process( '/not/a/valid/path' ) )->to_process();
	}
}

class Chrome_ProcessWindows extends Chrome_Process {

	protected function on_mac(): bool {
			return false;
	}

	protected function on_windows(): bool {
			return true;
	}

	protected function operating_system_id(): string {
			return 'win';
	}
}

class Chrome_ProcessDarwinIntel extends Chrome_Process {

	protected function on_mac(): bool {
			return true;
	}

	protected function on_windows(): bool {
			return false;
	}

	protected function operating_system_id(): string {
			return 'mac-intel';
	}
}

class Chrome_ProcessDarwinArm extends Chrome_Process {

	protected function on_mac(): bool {
			return true;
	}

	protected function on_windows(): bool {
			return false;
	}

	protected function operatingSystemId() {
			return 'mac-arm';
	}
}

class Chrome_ProcessLinux extends Chrome_Process {

	protected function on_arm_mac(): bool {
			return false;
	}

	protected function on_intel_mac(): bool {
			return false;
	}

	protected function on_windows(): bool {
			return false;
	}

	protected function operating_system_id(): string {
			return 'linux';
	}
}
