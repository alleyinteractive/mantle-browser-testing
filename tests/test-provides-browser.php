<?php

namespace Mantle\Browser_Testing\Tests;

use Mantle\Browser_Testing\Concerns\Provides_Browser;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use stdClass;

use function Mantle\Support\Helpers\collect;

class Test_Provides_Browser extends TestCase {

	use Provides_Browser;

	/**
	 * @dataProvider getTestData
	 */
	public function test_capture_failures_for() {
		$browser = m::mock( stdClass::class );
		$browser->shouldReceive( 'screenshot' )->with(
			'failure-Mantle_Browser_Testing_Tests_Test_Provides_Browser_test_capture_failures_for-0',
		);
		$browsers = collect( [ $browser ] );

		$this->captureFailuresFor( $browsers );

		$this->addToAssertionCount(
			\Mockery::getContainer()->mockery_getExpectationCount()
		);
	}

	/**
	 * @dataProvider getTestData
	 */
	public function test_store_console_logs_for() {
		$browser = m::mock( stdClass::class );
		$browser->shouldReceive( 'storeConsoleLog' )->with(
			'Mantle_Browser_Testing_Tests_Test_Provides_Browser_test_store_console_logs_for-0'
		);
		$browsers = collect( [ $browser ] );

		$this->storeConsoleLogsFor( $browsers );

		$this->addToAssertionCount(
			\Mockery::getContainer()->mockery_getExpectationCount()
		);
	}

	public function getTestData() {
		return [
			[ 'foo' ],
		];
	}

	/**
	 * Implementation of abstract ProvidesBrowser::driver().
	 */
	protected function driver() {
	}
}
