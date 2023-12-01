<?php
/**
 * Browser_Testing_Service_Provider class file.
 *
 * @package mantle-browser-testing
 */

namespace Mantle\Browser_Testing;

use Mantle\Contracts\Support\Isolated_Service_Provider;
use Mantle\Facade\Route;
use Mantle\Support\Service_Provider;

/**
 * Browser Testing Service Provider
 */
class Browser_Testing_Service_Provider extends Service_Provider implements Isolated_Service_Provider {
	/**
	 * Register console commands.
	 */
	public function register() {
		if ( $this->app->is_running_in_console() ) {
			$this->add_command(
				[
					Console\Chrome_Driver_Command::class,
					Console\Make_Command::class,
					Console\Install_Command::class,
				]
			);
		}

		if ( ! $this->app->is_environment( 'production' ) ) {
			$this->register_browser_testing_routes();
		}
	}

	/**
	 * Register HTTP routes used to login/logout the user.
	 */
	protected function register_browser_testing_routes() {
		Route::group(
			[
				'prefix' => config( 'browser-testing.path', '_browser-testing' ),
			],
			function () {
				Route::get(
					'/user',
					[
						'as'         => 'browser-testing.user',
						'middleware' => 'rest-api',
						'callback'   => [ Http\Controllers\User_Controller::class, 'user' ],
					],
				);

				Route::get(
					'/login/{user_id}',
					[
						'as'         => 'browser-testing.login',
						'middleware' => 'web',
						'callback'   => [ Http\Controllers\User_Controller::class, 'login' ],
					]
				);

				Route::get(
					'/logout',
					[
						'as'         => 'browser-testing.logout',
						'middleware' => 'web',
						'callback'   => [ Http\Controllers\User_Controller::class, 'logout' ],
					]
				);
			}
		);
	}
}
