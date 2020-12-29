<?php
namespace Mantle\Browser_Testing;

use Exception;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Mantle\Browser_Testing\Chrome\Supports_Chrome;
use Mantle\Browser_Testing\Concerns\Provides_Browser;
use Mantle\Framework\Testing\Test_Case as Framework_Test_Case;

abstract class Test_Case extends Framework_Test_Case {
	use Provides_Browser, Supports_Chrome;

	/**
	 * Setup the Browser Testing Test Case.
	 */
	protected function setUp(): void {
		parent::setUp();

		Browser::$base_url = $this->base_url();

		// todo: remove
		Browser::$base_url = 'https://mantle.test';

		Browser::$store_screenshots_at = base_path( 'tests/browser/screenshots' );

		Browser::$store_console_log_at = base_path( 'tests/browser/console' );

		Browser::$store_source_at = base_path( 'tests/browser/source' );

		Browser::$user_resolver = function() {
			return $this->user();
		};
	}

	/**
	 * Create the RemoteWebDriver instance.
	 *
	 * @return \Facebook\WebDriver\Remote\RemoteWebDriver
	 */
	protected function driver(): RemoteWebDriver {
		return RemoteWebDriver::create(
			'http://localhost:9515',
			DesiredCapabilities::chrome()
		);
	}

	/**
	 * Determine the application's base URL.
	 *
	 * @return string
	 */
	protected function base_url(): string {
		return rtrim( home_url(), '/' );
	}

	/**
	 * Return the default user to authenticate.
	 *
	 * @return \App\User|int|null
	 *
	 * @throws \Exception
	 */
	protected function user() {
		throw new Exception( 'User resolver has not been set.' );
	}
}
