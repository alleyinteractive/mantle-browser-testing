<?php

namespace Mantle\Browser_Testing\Concerns;

trait Interacts_With_Javascript {

	/**
	 * Execute JavaScript within the browser.
	 *
	 * @param  string|array $scripts
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
