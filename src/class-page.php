<?php
/**
 * Page class file.
 *
 * @package mantle-browser-testing
 */

namespace Mantle\Browser_Testing;

/**
 * Page Instance
 */
abstract class Page {

	/**
	 * Get the URL for the page.
	 *
	 * @return string
	 */
	abstract public function url();

	/**
	 * Assert that the browser is on the page.
	 *
	 * @param  \Mantle\Browser_Testing\Browser $browser Browser instance.
	 * @return void
	 */
	public function assert( Browser $browser ): void {
		// ...
	}

	/**
	 * Get the element shortcuts for the page.
	 *
	 * @return array
	 */
	public function elements(): array {
		return [];
	}

	/**
	 * Get the global element shortcuts for the site.
	 *
	 * @return array
	 */
	public static function site_elements(): array {
		return [];
	}
}
