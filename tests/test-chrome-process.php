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
		$process = ( new ChromeProcessWindows() )->to_process();

		$this->assertInstanceOf( Process::class, $process );
		$this->assertStringContainsString( 'chromedriver-win.exe', $process->getCommandLine() );
	}

	public function test_build_process_for_darwin() {
		$process = ( new ChromeProcessDarwin() )->to_process();

		$this->assertInstanceOf( Process::class, $process );
		$this->assertStringContainsString( 'chromedriver-mac', $process->getCommandLine() );
	}

	public function test_build_process_for_linux() {
		$process = ( new ChromeProcessLinux() )->to_process();

		$this->assertInstanceOf( Process::class, $process );
		$this->assertStringContainsString( 'chromedriver-linux', $process->getCommandLine() );
	}

	public function test_invalid_path() {
		$this->expectException( RuntimeException::class );

		( new Chrome_Process( '/not/a/valid/path' ) )->to_process();
	}
}

class ChromeProcessWindows extends Chrome_Process {

	protected function on_windows() {
		return true;
	}
}

class ChromeProcessDarwin extends Chrome_Process {

	protected function on_mac() {
		return true;
	}

	protected function on_windows() {
		return false;
	}
}

class ChromeProcessLinux extends Chrome_Process {

	protected function on_mac() {
		return false;
	}

	protected function on_windows() {
		return false;
	}
}
