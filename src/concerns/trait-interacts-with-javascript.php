<?php
/**
 * Interacts_With_Javascript trait file.
 *
 * @package mantle-browser-testing
 */

namespace Mantle\Browser_Testing\Concerns;

/**
 * Concern for Javascript interactions.
 */
trait Interacts_With_Javascript {

	/**
	 * Execute JavaScript within the browser.
	 *
	 * @param string|array $scripts Scripts to execute.
	 * @return array
	 */
	public function script( $scripts ) {
		return collect( (array) $scripts )->map(
			function ( $script ) {
				return $this->driver->executeScript( $script );
			}
		)->all();
	}
}
