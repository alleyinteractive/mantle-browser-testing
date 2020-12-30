<?php
/**
 * Component class file.
 *
 * @package mantle-browser-testing
 */

namespace Mantle\Browser_Testing;

/**
 * Component Base Class
 */
abstract class Component {

	/**
	 * Get the root selector associated with this component.
	 *
	 * @return string
	 */
	abstract public function selector();

	/**
	 * Assert that the current page contains this component.
	 *
	 * @param  \Mantle\Browser_Testing\Browser $browser Browser instance.
	 * @return void
	 */
	public function assert( Browser $browser ) { }

	/**
	 * Get the element shortcuts for the page.
	 *
	 * @return array
	 */
	public function elements() {
		return [];
	}

	/**
	 * Allow this class to be used in place of a selector string.
	 *
	 * @return string
	 */
	public function __toString() {
		return '';
	}
}
