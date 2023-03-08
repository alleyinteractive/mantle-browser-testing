<?php
/**
 * Provides_Browser trait file.
 *
 * @package mantle-browser-testing
 */

namespace Mantle\Browser_Testing\Concerns;

use Closure;
use Exception;
use Mantle\Support\Collection;
use Mantle\Browser_Testing\Browser;
use ReflectionFunction;
use Throwable;

/**
 * Concern for browser interaction.
 */
trait Provides_Browser {

	/**
	 * All of the active browser instances.
	 *
	 * @var array
	 */
	protected static $browsers = [];

	/**
	 * The callbacks that should be run on class tear down.
	 *
	 * @var array
	 */
	protected static $after_class_callbacks = [];

	/**
	 * Tear down the Dusk test case class.
	 *
	 * @afterClass
	 * @return void
	 */
	public static function tearDownBrowserTestingClass() {
		static::closeAll();

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
	public function browse( Closure $callback ) {
		$browsers = $this->createBrowsersFor( $callback );

		try {
			$callback( ...$browsers->all() );
		} catch ( Exception $e ) {
			$this->captureFailuresFor( $browsers );
			$this->storeSourceLogsFor( $browsers );

			throw $e;
		} catch ( Throwable $e ) {
			$this->captureFailuresFor( $browsers );
			$this->storeSourceLogsFor( $browsers );

			throw $e;
		} finally {
			$this->storeConsoleLogsFor( $browsers );

			static::$browsers = $this->closeAllButPrimary( $browsers );
		}
	}

	/**
	 * Create the browser instances needed for the given callback.
	 *
	 * @param  \Closure $callback Callback to invoke.
	 * @return array
	 */
	protected function createBrowsersFor( Closure $callback ) {
		if ( count( static::$browsers ) === 0 ) {
			static::$browsers = collect( [ $this->newBrowser( $this->createWebDriver() ) ] );
		}

		$additional = $this->browsersNeededFor( $callback ) - 1;

		for ( $i = 0; $i < $additional; $i++ ) {
			static::$browsers->push( $this->newBrowser( $this->createWebDriver() ) );
		}

		return static::$browsers;
	}

	/**
	 * Create a new Browser instance.
	 *
	 * @param  \Facebook\WebDriver\Remote\RemoteWebDriver $driver Driver instance.
	 * @return \Mantle\Browser_Testing\Browser
	 */
	protected function newBrowser( $driver ) {
		return new Browser( $driver );
	}

	/**
	 * Get the number of browsers needed for a given callback.
	 *
	 * @param  \Closure $callback Callback to invoke.
	 * @return int
	 */
	protected function browsersNeededFor( Closure $callback ) {
		return ( new ReflectionFunction( $callback ) )->getNumberOfParameters();
	}

	/**
	 * Capture failure screenshots for each browser.
	 *
	 * @param  \Mantle\Framework\Support\Collection $browsers Browsers to use.
	 * @return void
	 */
	protected function captureFailuresFor( $browsers ) {
		$browsers->each(
			function ( $browser, $key ) {
				if ( property_exists( $browser, 'fit_on_failure' ) && $browser->fit_on_failure ) {
					$browser->fit_content();
				}

				$name = $this->getCallerName();

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
	protected function storeConsoleLogsFor( $browsers ) {
		$browsers->each(
			function ( $browser, $key ) {
				$name = $this->getCallerName();

				$browser->storeConsoleLog( $name . '-' . $key );
			}
		);
	}

	/**
	 * Store the source code for the given browsers (if necessary).
	 *
	 * @param  \Mantle\Framework\Support\Collection $browsers Browsers to use.
	 * @return void
	 */
	protected function storeSourceLogsFor( $browsers ) {
		$browsers->each(
			function ( $browser, $key ) {
				if ( property_exists( $browser, 'made_source_assertion' ) &&
				$browser->made_source_assertion ) {
					$browser->storeSource( $this->getCallerName() . '-' . $key );
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
	protected function closeAllButPrimary( $browsers ) {
		$browsers->slice( 1 )->each->quit();

		return $browsers->take( 1 );
	}

	/**
	 * Close all of the active browsers.
	 *
	 * @return void
	 */
	public static function closeAll() {
		Collection::make( static::$browsers )->each->quit();

		static::$browsers = collect();
	}

	/**
	 * Create the remote web driver instance.
	 *
	 * @return \Facebook\WebDriver\Remote\RemoteWebDriver
	 */
	protected function createWebDriver() {
		return retry(
			5,
			function () {
				return $this->driver();
			},
			50
		);
	}

	/**
	 * Get the browser caller name.
	 *
	 * @return string
	 */
	protected function getCallerName() {
		return str_replace( '\\', '_', get_class( $this ) ) . '_' . $this->getName( false );
	}

	/**
	 * Create the RemoteWebDriver instance.
	 *
	 * @return \Facebook\WebDriver\Remote\RemoteWebDriver
	 */
	abstract protected function driver();
}
