<?php
/**
 * User_Controller class file.
 *
 * @package mantle-browser-testing
 */

namespace Mantle\Browser_Testing\Http\Controllers;

use Mantle\Framework\Http\Controller;

/**
 * User Controller to login and logout users for browser testing.
 *
 * Not used in production.
 */
class User_Controller extends Controller {
	/**
	 * Retrieve information about the current user.
	 *
	 * @return array
	 */
	public function user() {
		return [
			'user_id' => get_current_user_id(),
		];
	}

	/**
	 * Log a user into the application.
	 *
	 * @param int $user_id User ID.
	 * @return string
	 */
	public function login( $user_id ) {
		wp_set_auth_cookie( $user_id, true );
		wp_set_current_user( $user_id );

		return "Logged In as {$user_id}";
	}

	/**
	 * Log the user out of the application.
	 *
	 * @return string
	 */
	public function logout() {
		wp_logout();

		return 'Logged out';
	}
}
