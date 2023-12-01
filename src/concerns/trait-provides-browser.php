<?php
/**
 * Provides_Browser trait file.
 *
 * @package mantle-browser-testing
 */

namespace Mantle\Browser_Testing\Concerns;

use Closure;
use Exception;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Mantle\Browser_Testing\Browser;
use Mantle\Support\Collection;
use PHPUnit\Runner\Version;
use ReflectionFunction;
use Throwable;

/**
 * Concern for browser interaction.
 *
 * @mixin \Mantle\Browser_Testing\Test_Case
 */
trait Provides_Browser {

	/**
	 * All of the active browser instances.
	 *
	 * @var \Mantle\Support\Collection<int, \Mantle\Browser_Testing\Browser>
	 */
	protected static Collection $browsers;

	/**
	 * The callbacks that should be run on class tear down.
	 *
	 * @var callable[]
	 */
	protected static array $after_class_callbacks = [];

	/**
	 * Tear down the Dusk test case class.
	 *
	 * @afterClass
	 * @return void
	 */
	public static function tearDownBrowserTestingClass() {
		static::close_all();

		foreach ( static::$after_class_callbacks as $callback ) {
			$callback();
		}
	}

	/**
	 * Register an "after class" tear down callback.
	 *
	 * @param  \Closure $callback Callback to invoke.
	 * @return void
	 */
	public static function afterClass( Closure $callback ) {
		static::$after_class_callbacks[] = $callback;
	}

	/**
	 * Create a new browser instance.
	 *
	 * @param  \Closure $callback Callback to invoke.
	 * @return void
	 *
	 * @throws \Exception Thrown on callback exception.
	 * @throws \Throwable Thrown on callback exception.
	 */
	public function browse( Closure $callback ): void {
		$browsers = $this->create_browsers_for( $callback );

		try {
			$callback( ...$browsers->all() );
		} catch ( Exception $e ) {
			$this->capture_failures_for( $browsers );
			$this->store_source_logs_for( $browsers );

			throw $e;
		} catch ( Throwable $e ) {
			$this->capture_failures_for( $browsers );
			$this->store_source_logs_for( $browsers );

			throw $e;
		} finally {
			$this->store_console_logs_for( $browsers );

			static::$browsers = $this->close_all_but_primary( $browsers );
		}
	}

	/**
	 * Create the browser instances needed for the given callback.
	 *
	 * @param  \Closure $callback Callback to invoke.
	 * @return array
	 */
	protected function create_browsers_for( Closure $callback ): array {
		if ( ! isset( static::$browsers ) ) {
			static::$browsers = new Collection();
		}

		if ( count( static::$browsers ) === 0 ) {
			static::$browsers = collect( [ $this->new_browser( $this->create_web_driver() ) ] );
		}

		$additional = $this->browsers_needed_for( $callback ) - 1;

		for ( $i = 0; $i < $additional; $i++ ) {
			static::$browsers->push( $this->new_browser( $this->create_web_driver() ) );
		}

		return static::$browsers;
	}

	/**
	 * Create a new Browser instance.
	 *
	 * @param  \Facebook\WebDriver\Remote\RemoteWebDriver $driver Driver instance.
	 * @return \Mantle\Browser_Testing\Browser
	 */
	protected function new_browser( $driver ) {
		return new Browser( $driver );
	}

	/**
	 * Get the number of browsers needed for a given callback.
	 *
	 * @param  \Closure $callback Callback to invoke.
	 * @return int
	 */
	protected function browsers_needed_for( Closure $callback ) {
		return ( new ReflectionFunction( $callback ) )->getNumberOfParameters();
	}

	/**
	 * Capture failure screenshots for each browser.
	 *
	 * @param  \Mantle\Framework\Support\Collection $browsers Browsers to use.
	 * @return void
	 */
	protected function capture_failures_for( $browsers ) {
		$browsers->each(
			function ( $browser, $key ) {
				if ( property_exists( $browser, 'fit_on_failure' ) && $browser->fit_on_failure ) {
					$browser->fit_content();
				}

				$name = $this->get_caller_name();

				$browser->screenshot( 'failure-' . $name . '-' . $key );
			}
		);
	}

	/**
	 * Store the console output for the given browsers.
	 *
	 * @param  \Mantle\Framework\Support\Collection $browsers Browsers to use.
	 * @return void
	 */
	protected function store_console_logs_for( $browsers ) {
		$browsers->each(
			function ( $browser, $key ) {
				$name = $this->get_caller_name();

				$browser->store_console_log( $name . '-' . $key );
			}
		);
	}

	/**
	 * Store the source code for the given browsers (if necessary).
	 *
	 * @param  \Mantle\Framework\Support\Collection $browsers Browsers to use.
	 * @return void
	 */
	protected function store_source_logs_for( $browsers ) {
		$browsers->each(
			function ( $browser, $key ) {
				if ( property_exists( $browser, 'made_source_assertion' ) &&
				$browser->made_source_assertion ) {
					$browser->storeSource( $this->get_caller_name() . '-' . $key );
				}
			}
		);
	}

	/**
	 * Close all of the browsers except the primary (first) one.
	 *
	 * @param  \Mantle\Framework\Support\Collection $browsers Browsers to use.
	 * @return \Mantle\Framework\Support\Collection
	 */
	protected function close_all_but_primary( $browsers ) {
		$browsers->slice( 1 )->each->quit();

		return $browsers->take( 1 );
	}

	/**
	 * Close all of the active browsers.
	 *
	 * @return void
	 */
	public static function close_all(): void {
		Collection::make( static::$browsers ?? [] )->each(
			fn ( $browser ) => $browser->quit(),
		);

		static::$browsers = new Collection();
	}

	/**
	 * Create the remote web driver instance.
	 *
	 * @return \Facebook\WebDriver\Remote\RemoteWebDriver
	 */
	protected function create_web_driver(): RemoteWebDriver {
		return retry( 5, fn () => $this->driver(), 50 );
	}

	/**
	 * Get the browser caller name.
	 *
	 * @return string
	 */
	protected function get_caller_name(): string {
		$name = version_compare(Version::id(), '10', '>=')
			? $this->name()
			: $this->getName(false); // @phpstan-ignore-line

		return str_replace('\\', '_', substr(get_class($this), 0, 70)).'_'.substr($name, 0, 70);
	}

	/**
	 * Create the RemoteWebDriver instance.
	 *
	 * @return \Facebook\WebDriver\Remote\RemoteWebDriver
	 */
	abstract protected function driver();
}
