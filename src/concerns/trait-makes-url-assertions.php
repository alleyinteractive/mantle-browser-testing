<?php
/**
 * Makes_Url_Assertions trait file.
 *
 * @package mantle-browser-testing
 */

namespace Mantle\Browser_Testing\Concerns;

use Illuminate\Support\Arr;
use PHPUnit\Framework\Assert as PHPUnit;
use PHPUnit\Framework\Constraint\RegularExpression;

/**
 * Concern for URL assertions.
 */
trait Makes_Url_Assertions {

	/**
	 * Assert that the current URL (without the query string) matches the given string.
	 *
	 * @param  string $url URL to compare.
	 * @return $this
	 */
	public function assertUrlIs( $url ) {
		$pattern = str_replace( '\*', '.*', preg_quote( $url, '/' ) );

		$segments = wp_parse_url( $this->driver->getCurrentURL() );

		$current = sprintf(
			'%s://%s%s%s',
			$segments['scheme'],
			$segments['host'],
			Arr::get( $segments, 'port', '' ) ? ':' . $segments['port'] : '',
			Arr::get( $segments, 'path', '' )
		);

		PHPUnit::assertThat(
			$current,
			new RegularExpression( '/^' . $pattern . '$/u' ),
			"Actual URL [{$this->driver->getCurrentURL()}] does not equal expected URL [{$url}]."
		);

		return $this;
	}

	/**
	 * Assert that the current URL scheme matches the given scheme.
	 *
	 * @param  string $scheme Scheme to compare.
	 * @return $this
	 */
	public function assertSchemeIs( $scheme ) {
		$pattern = str_replace( '\*', '.*', preg_quote( $scheme, '/' ) );

		$actual = wp_parse_url( $this->driver->getCurrentURL(), PHP_URL_SCHEME ) ?? '';

		PHPUnit::assertThat(
			$actual,
			new RegularExpression( '/^' . $pattern . '$/u' ),
			"Actual scheme [{$actual}] does not equal expected scheme [{$pattern}]."
		);

		return $this;
	}

	/**
	 * Assert that the current URL scheme does not match the given scheme.
	 *
	 * @param  string $scheme Scheme to compare.
	 * @return $this
	 */
	public function assertSchemeIsNot( $scheme ) {
		$actual = wp_parse_url( $this->driver->getCurrentURL(), PHP_URL_SCHEME ) ?? '';

		PHPUnit::assertNotEquals(
			$scheme,
			$actual,
			"Scheme [{$scheme}] should not equal the actual value."
		);

		return $this;
	}

	/**
	 * Assert that the current URL host matches the given host.
	 *
	 * @param  string $host Host to compare.
	 * @return $this
	 */
	public function assertHostIs( $host ) {
		$pattern = str_replace( '\*', '.*', preg_quote( $host, '/' ) );

		$actual = wp_parse_url( $this->driver->getCurrentURL(), PHP_URL_HOST ) ?? '';

		PHPUnit::assertThat(
			$actual,
			new RegularExpression( '/^' . $pattern . '$/u' ),
			"Actual host [{$actual}] does not equal expected host [{$pattern}]."
		);

		return $this;
	}

	/**
	 * Assert that the current URL host does not match the given host.
	 *
	 * @param  string $host Host to compare.
	 * @return $this
	 */
	public function assertHostIsNot( $host ) {
		$actual = wp_parse_url( $this->driver->getCurrentURL(), PHP_URL_HOST ) ?? '';

		PHPUnit::assertNotEquals(
			$host,
			$actual,
			"Host [{$host}] should not equal the actual value."
		);

		return $this;
	}

	/**
	 * Assert that the current URL port matches the given port.
	 *
	 * @param  string $port Port to compare.
	 * @return $this
	 */
	public function assertPortIs( $port ) {
		$pattern = str_replace( '\*', '.*', preg_quote( $port, '/' ) );

		$actual = (string) wp_parse_url( $this->driver->getCurrentURL(), PHP_URL_PORT ) ?? '';

		PHPUnit::assertThat(
			$actual,
			new RegularExpression( '/^' . $pattern . '$/u' ),
			"Actual port [{$actual}] does not equal expected port [{$pattern}]."
		);

		return $this;
	}

	/**
	 * Assert that the current URL port does not match the given port.
	 *
	 * @param  string $port Port to compare.
	 * @return $this
	 */
	public function assertPortIsNot( $port ) {
		$actual = wp_parse_url( $this->driver->getCurrentURL(), PHP_URL_PORT ) ?? '';

		PHPUnit::assertNotEquals(
			$port,
			$actual,
			"Port [{$port}] should not equal the actual value."
		);

		return $this;
	}

	/**
	 * Assert that the current URL path begins with the given path.
	 *
	 * @param  string $path Path to compare.
	 * @return $this
	 */
	public function assertPathBeginsWith( $path ) {
		$actual = wp_parse_url( $this->driver->getCurrentURL(), PHP_URL_PATH ) ?? '';

		PHPUnit::assertStringStartsWith(
			$path,
			$actual,
			"Actual path [{$actual}] does not begin with expected path [{$path}]."
		);

		return $this;
	}

	/**
	 * Assert that the current path matches the given path.
	 *
	 * @param  string $path Path to compare.
	 * @return $this
	 */
	public function assertPathIs( $path ) {
		$pattern = str_replace( '\*', '.*', preg_quote( $path, '/' ) );

		$actual = wp_parse_url( $this->driver->getCurrentURL(), PHP_URL_PATH ) ?? '';

		PHPUnit::assertThat(
			$actual,
			new RegularExpression( '/^' . $pattern . '$/u' ),
			"Actual path [{$actual}] does not equal expected path [{$path}]."
		);

		return $this;
	}

	/**
	 * Assert that the current path does not match the given path.
	 *
	 * @param  string $path Path to compare.
	 * @return $this
	 */
	public function assertPathIsNot( $path ) {
		$actual = wp_parse_url( $this->driver->getCurrentURL(), PHP_URL_PATH ) ?? '';

		PHPUnit::assertNotEquals(
			$path,
			$actual,
			"Path [{$path}] should not equal the actual value."
		);

		return $this;
	}

	/**
	 * Assert that the current URL matches the given named route's URL.
	 *
	 * @param  string $route Route to compare.
	 * @param  array  $parameters Route parameters.
	 * @return $this
	 */
	public function assertRouteIs( $route, $parameters = [] ) {
		return $this->assertPathIs( route( $route, $parameters, false ) );
	}

	/**
	 * Assert that the given query string parameter is present and has a given value.
	 *
	 * @param  string $name Query variable name.
	 * @param  string $value Query variable value.
	 * @return $this
	 */
	public function assertQueryStringHas( $name, $value = null ) {
		$output = $this->assertHasQueryStringParameter( $name );

		if ( is_null( $value ) ) {
			return $this;
		}

		$parsed_output_name = is_array( $output[ $name ] ) ? implode( ',', $output[ $name ] ) : $output[ $name ];

		$value = is_array( $value ) ? implode( ',', $value ) : $value;

		PHPUnit::assertEquals(
			$value,
			$parsed_output_name,
			"Query string parameter [{$name}] had value [{$parsed_output_name}], but expected [{$value}]."
		);

		return $this;
	}

	/**
	 * Assert that the given query string parameter is missing.
	 *
	 * @param  string $name Query variable name.
	 * @return $this
	 */
	public function assertQueryStringMissing( $name ) {
		$parsed_url = wp_parse_url( $this->driver->getCurrentURL() );

		if ( ! array_key_exists( 'query', $parsed_url ) ) {
			PHPUnit::assertTrue( true );

			return $this;
		}

		parse_str( $parsed_url['query'], $output );

		PHPUnit::assertArrayNotHasKey(
			$name,
			$output,
			"Found unexpected query string parameter [{$name}] in [" . $this->driver->getCurrentURL() . '].'
		);

		return $this;
	}

	/**
	 * Assert that the URL's current hash fragment matches the given fragment.
	 *
	 * @param  string $fragment Fragment to compare.
	 * @return $this
	 */
	public function assertFragmentIs( $fragment ) {
		$pattern = preg_quote( $fragment, '/' );

		$actual = (string) wp_parse_url( $this->driver->executeScript( 'return window.location.href;' ), PHP_URL_FRAGMENT );

		PHPUnit::assertThat(
			$actual,
			new RegularExpression( '/^' . str_replace( '\*', '.*', $pattern ) . '$/u' ),
			"Actual fragment [{$actual}] does not equal expected fragment [{$fragment}]."
		);

		return $this;
	}

	/**
	 * Assert that the URL's current hash fragment begins with the given fragment.
	 *
	 * @param  string $fragment Fragment to compare.
	 * @return $this
	 */
	public function assertFragmentBeginsWith( $fragment ) {
		$actual = (string) wp_parse_url( $this->driver->executeScript( 'return window.location.href;' ), PHP_URL_FRAGMENT );

		PHPUnit::assertStringStartsWith(
			$fragment,
			$actual,
			"Actual fragment [$actual] does not begin with expected fragment [$fragment]."
		);

		return $this;
	}

	/**
	 * Assert that the URL's current hash fragment does not match the given fragment.
	 *
	 * @param  string $fragment Fragment to compare.
	 * @return $this
	 */
	public function assertFragmentIsNot( $fragment ) {
		$actual = (string) wp_parse_url( $this->driver->executeScript( 'return window.location.href;' ), PHP_URL_FRAGMENT );

		PHPUnit::assertNotEquals(
			$fragment,
			$actual,
			"Fragment [{$fragment}] should not equal the actual value."
		);

		return $this;
	}

	/**
	 * Assert that the given query string parameter is present.
	 *
	 * @param  string $name Query string parameter.
	 * @return array
	 */
	protected function assertHasQueryStringParameter( $name ) {
		$parsed = parse_url( $this->driver->getCurrentURL() );

		PHPUnit::assertArrayHasKey(
			'query',
			$parsed,
			'Did not see expected query string in [' . $this->driver->getCurrentURL() . '].'
		);

		parse_str( $parsed['query'], $output );

		PHPUnit::assertArrayHasKey(
			$name,
			$output,
			"Did not see expected query string parameter [{$name}] in [" . $this->driver->getCurrentURL() . '].'
		);

		return $output;
	}
}
