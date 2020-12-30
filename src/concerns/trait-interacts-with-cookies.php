<?php
/**
 * Interacts_With_Cookies class file.
 *
 * @package mantle-browser-testing
 */

namespace Mantle\Browser_Testing\Concerns;

use DateTimeInterface;
use Facebook\WebDriver\Exception\NoSuchCookieException;

/**
 * Concern for interacting with cookies.
 */
trait Interacts_With_Cookies {

	/**
	 * Get or set an cookie's value.
	 *
	 * @param  string                     $name Cookie name.
	 * @param  string|null                $value Cookie value.
	 * @param  int|DateTimeInterface|null $expiry Expiration.
	 * @param  array                      $options Options to pass.
	 * @return string
	 */
	public function plainCookie( $name, $value = null, $expiry = null, array $options = [] ) {
		if ( ! is_null( $value ) ) {
			return $this->addCookie( $name, $value, $expiry, $options, false );
		}

		try {
			$cookie = $this->driver->manage()->getCookieNamed( $name );
		} catch ( NoSuchCookieException $e ) {
			$cookie = null;
		}

		if ( $cookie ) {
			return rawurldecode( $cookie['value'] );
		}
	}

	/**
	 * Add the given cookie.
	 *
	 * @param  string                     $name Cookie name.
	 * @param  string                     $value Cookie value.
	 * @param  int|DateTimeInterface|null $expiry Expiration.
	 * @param  array                      $options Options to pass.
	 * @return static
	 */
	public function addCookie( $name, $value, $expiry = null, array $options = [] ) {
		if ( $expiry instanceof DateTimeInterface ) {
			$expiry = $expiry->getTimestamp();
		}

		$this->driver->manage()->addCookie(
			array_merge( $options, compact( 'expiry', 'name', 'value' ) )
		);

		return $this;
	}

	/**
	 * Delete the given cookie.
	 *
	 * @param string $name Cookie name.
	 * @return static
	 */
	public function deleteCookie( $name ) {
		$this->driver->manage()->deleteCookieNamed( $name );

		return $this;
	}
}
