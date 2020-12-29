<?php
/**
 * Browser_Testing_Service_Provider class file.
 *
 * @package mantle-browser-testing
 */

namespace Mantle\Browser_Testing;

use Mantle\Framework\Service_Provider;

/**
 * Browser Testing Service Provider
 */
class Browser_Testing_Service_Provider extends Service_Provider {
	/**
	 * Register console commands.
	 */
	public function register() {
		if ( $this->app->is_running_in_console() ) {
			$this->add_command(
				[
					Console\Chrome_Driver_Command::class,
					Console\Install_Command::class,
				]
			);
		}
	}
}
