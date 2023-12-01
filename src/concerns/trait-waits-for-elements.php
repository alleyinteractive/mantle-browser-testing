<?php
/**
 * Waits_For_Elements trait file.
 *
 * @package mantle-browser-testing
 * phpcs:disable Squiz.Commenting.FunctionComment.EmptyThrows
 * phpcs:disable Squiz.Commenting.EmptyCatchComment.Missing
 * phpcs:disable Generic.CodeAnalysis.EmptyStatement.DetectedCatch
 */

namespace Mantle\Browser_Testing\Concerns;

use Carbon\Carbon;
use Closure;
use Exception;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Exception\TimeOutException;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Mantle\Support\Arr;
use Mantle\Support\Str;

trait Waits_For_Elements {

	/**
	 * Execute the given callback in a scoped browser once the selector is available.
	 *
	 * @param  string   $selector CSS selector.
	 * @param  \Closure $callback Callback to invoke.
	 * @param  int      $seconds Seconds to wait.
	 * @return $this
	 *
	 * @throws \Facebook\WebDriver\Exception\TimeOutException Thrown on timeout.
	 */
	public function whenAvailable( $selector, Closure $callback, $seconds = null ) {
		return $this->waitFor( $selector, $seconds )->with( $selector, $callback );
	}

	/**
	 * Wait for the given selector to be visible.
	 *
	 * @param  string $selector CSS selector.
	 * @param  int    $seconds Seconds to wait.
	 * @return $this
	 *
	 * @throws \Facebook\WebDriver\Exception\TimeOutException Thrown on timeout.
	 */
	public function waitFor( $selector, $seconds = null ) {
		$message = $this->formatTimeOutMessage( 'Waited %s seconds for selector', $selector );

		return $this->waitUsing(
			$seconds,
			100,
			function () use ( $selector ) {
				return $this->resolver->findOrFail( $selector )->isDisplayed();
			},
			$message
		);
	}

	/**
	 * Wait for the given selector to be removed.
	 *
	 * @param  string $selector CSS selector.
	 * @param  int    $seconds Seconds to wait.
	 * @return $this
	 *
	 * @throws \Facebook\WebDriver\Exception\TimeOutException Thrown on timeout.
	 */
	public function waitUntilMissing( $selector, $seconds = null ) {
		$message = $this->formatTimeOutMessage( 'Waited %s seconds for removal of selector', $selector );

		return $this->waitUsing(
			$seconds,
			100,
			function () use ( $selector ) {
				try {
					$missing = ! $this->resolver->findOrFail( $selector )->isDisplayed();
				} catch ( NoSuchElementException $e ) {
					$missing = true;
				}

				return $missing;
			},
			$message
		);
	}

	/**
	 * Wait for the given text to be removed.
	 *
	 * @param  string $text Text to compare.
	 * @param  int    $seconds Seconds to wait.
	 * @return $this
	 *
	 * @throws \Facebook\WebDriver\Exception\TimeOutException Thrown on timeout.
	 */
	public function waitUntilMissingText( $text, $seconds = null ) {
		$text = Arr::wrap( $text );

		$message = $this->formatTimeOutMessage( 'Waited %s seconds for removal of text', implode( "', '", $text ) );

		return $this->waitUsing(
			$seconds,
			100,
			function () use ( $text ) {
				return ! Str::contains( $this->resolver->findOrFail( '' )->getText(), $text );
			},
			$message
		);
	}

	/**
	 * Wait for the given text to be visible.
	 *
	 * @param  array|string $text Text to compare.
	 * @param  int          $seconds Seconds to wait.
	 * @return $this
	 *
	 * @throws \Facebook\WebDriver\Exception\TimeOutException Thrown on timeout.
	 */
	public function waitForText( $text, $seconds = null ) {
		$text = Arr::wrap( $text );

		$message = $this->formatTimeOutMessage( 'Waited %s seconds for text', implode( "', '", $text ) );

		return $this->waitUsing(
			$seconds,
			100,
			function () use ( $text ) {
				return Str::contains( $this->resolver->findOrFail( '' )->getText(), $text );
			},
			$message
		);
	}

	/**
	 * Wait for the given text to be visible inside the given selector.
	 *
	 * @param  string       $selector CSS selector.
	 * @param  array|string $text Text to compare.
	 * @param  int          $seconds Seconds to wait.
	 * @return $this
	 *
	 * @throws \Facebook\WebDriver\Exception\TimeOutException Thrown on timeout.
	 */
	public function waitForTextIn( $selector, $text, $seconds = null ) {
		$message = 'Waited %s seconds for text "' . $text . '" in selector ' . $selector;

		return $this->waitUsing(
			$seconds,
			100,
			function () use ( $selector, $text ) {
				return $this->assertSeeIn( $selector, $text );
			},
			$message
		);
	}

	/**
	 * Wait for the given link to be visible.
	 *
	 * @param  string $link Link to compare.
	 * @param  int    $seconds Seconds to wait.
	 * @return $this
	 *
	 * @throws \Facebook\WebDriver\Exception\TimeOutException Thrown on timeout.
	 */
	public function waitForLink( $link, $seconds = null ) {
		$message = $this->formatTimeOutMessage( 'Waited %s seconds for link', $link );

		return $this->waitUsing(
			$seconds,
			100,
			function () use ( $link ) {
				return $this->seeLink( $link );
			},
			$message
		);
	}

	/**
	 * Wait for the given location.
	 *
	 * @param  string $path Path to compare.
	 * @param  int    $seconds Seconds to wait.
	 * @return $this
	 *
	 * @throws \Facebook\WebDriver\Exception\TimeOutException Thrown on timeout.
	 */
	public function waitForLocation( $path, $seconds = null ) {
		$message = $this->formatTimeOutMessage( 'Waited %s seconds for location', $path );

		return $this->waitUntil( "window.location.pathname == '{$path}'", $seconds, $message );
	}

	/**
	 * Wait for the given location using a named route.
	 *
	 * @param  string $route Route to compare.
	 * @param  array  $parameters Route parameters.
	 * @param  int    $seconds Seconds to wait.
	 * @return $this
	 *
	 * @throws \Facebook\WebDriver\Exception\TimeOutException Thrown on timeout.
	 */
	public function waitForRoute( $route, $parameters = [], $seconds = null ) {
		return $this->waitForLocation( route( $route, $parameters, false ), $seconds );
	}

	/**
	 * Wait until the given script returns true.
	 *
	 * @param  string $script Script to compare.
	 * @param  int    $seconds Seconds to wait.
	 * @param  string $message Message to use.
	 * @return $this
	 *
	 * @throws \Facebook\WebDriver\Exception\TimeOutException Thrown on timeout.
	 */
	public function waitUntil( $script, $seconds = null, $message = null ) {
		if ( ! Str::starts_with( $script, 'return ' ) ) {
			$script = 'return ' . $script;
		}

		if ( ! Str::ends_with( $script, ';' ) ) {
			$script = $script . ';';
		}

		return $this->waitUsing(
			$seconds,
			100,
			function () use ( $script ) {
				return $this->driver->executeScript( $script );
			},
			$message
		);
	}

	/**
	 * Wait for a JavaScript dialog to open.
	 *
	 * @param  int $seconds Seconds to wait.
	 * @return $this
	 */
	public function waitForDialog( $seconds = null ) {
		$seconds = is_null( $seconds ) ? static::$wait_seconds : $seconds;

		$this->driver->wait( $seconds, 100 )->until(
			WebDriverExpectedCondition::alertIsPresent(),
			"Waited {$seconds} seconds for dialog."
		);

		return $this;
	}

	/**
	 * Wait for the current page to reload.
	 *
	 * @param  \Closure $callback Callback to invoke.
	 * @param  int      $seconds Seconds to wait.
	 * @return $this
	 *
	 * @throws \Facebook\WebDriver\Exception\TimeOutException Thrown on timeout.
	 */
	public function waitForReload( $callback = null, $seconds = null ) {
		$token = Str::random();

		$this->driver->executeScript( "window['{$token}'] = {};" );

		if ( $callback ) {
			$callback( $this );
		}

		return $this->waitUsing(
			$seconds,
			100,
			function () use ( $token ) {
				return $this->driver->executeScript( "return typeof window['{$token}'] === 'undefined';" );
			},
			'Waited %s seconds for page reload.'
		);
	}

	/**
	 * Wait for the given callback to be true.
	 *
	 * @param  int         $seconds Seconds to wait.
	 * @param  int         $interval Interval to use.
	 * @param  \Closure    $callback Callback to invoke.
	 * @param  string|null $message Message to compare.
	 * @return $this
	 *
	 * @throws \Facebook\WebDriver\Exception\TimeOutException Thrown on timeout.
	 */
	public function waitUsing( $seconds, $interval, Closure $callback, $message = null ) {
		$seconds = is_null( $seconds ) ? static::$wait_seconds : $seconds;

		$this->pause( $interval );

		$started = Carbon::now();

		while ( true ) {
			try {
				if ( $callback() ) {
					break;
				}
			} catch ( Exception $e ) {
			}

			if ( $started->lt( Carbon::now()->subSeconds( $seconds ) ) ) {
				throw new TimeOutException(
					$message
					? sprintf( $message, $seconds )
					: "Waited {$seconds} seconds for callback."
				);
			}

			$this->pause( $interval );
		}

		return $this;
	}

	/**
	 * Prepare custom TimeOutException message for sprintf().
	 *
	 * @param  string $message Message to format.
	 * @param  string $expected Expected message.
	 * @return string
	 */
	protected function formatTimeOutMessage( $message, $expected ) {
		return $message . ' [' . str_replace( '%', '%%', $expected ) . '].';
	}
}
