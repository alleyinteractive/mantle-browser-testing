<?php
/**
 * Interacts_With_Authentication trait file.
 *
 * @package mantle-browser-testing
 */

namespace Mantle\Browser_Testing\Concerns;

use Mantle\Browser_Testing\Browser;
use Mantle\Framework\Contracts\Database\Core_Object;
use PHPUnit\Framework\Assert as PHPUnit;

/**
 * Concern for interactions with authentication.
 */
trait Interacts_With_Authentication {

	/**
	 * Log into the application as the default user.
	 *
	 * @return $this
	 */
	public function login() {
		return $this->login_as( call_user_func( Browser::$user_resolver ) );
	}

	/**
	 * Log into the application using a given user ID or email.
	 *
	 * @param Core_Object|int $user_id User Model or ID.
	 * @return static
	 */
	public function login_as( $user_id ) {
		$user_id = $user_id instanceof Core_Object ? $user_id->id() : $user_id;

		return $this->visit(
			rtrim(
				route(
					'browser-testing.login',
					[
						'user_id' => $user_id,
					],
					$this->should_use_absolute_route_for_auth()
				)
			)
		);
	}

	/**
	 * Log out of the application.
	 *
	 * @return static
	 */
	public function logout() {
		return $this->visit( rtrim( route( 'browser-testing.logout', [], $this->should_use_absolute_route_for_auth() ), '/' ) );
	}

	/**
	 * Get the ID and the class name of the authenticated user.
	 *
	 * @return array
	 */
	protected function current_user_info(): array {
		$response = $this->visit( route( 'browser-testing.user', [], $this->should_use_absolute_route_for_auth() ) );

		return (array) json_decode( wp_strip_all_tags( $response->driver->getPageSource() ), true );
	}

	/**
	 * Assert that the user is authenticated.
	 *
	 * @return static
	 */
	public function assertAuthenticated() {
		PHPUnit::assertNotEmpty( $this->current_user_info()['user_id'] ?? null, 'The user is not authenticated.' );

		return $this;
	}

	/**
	 * Assert that the user is not authenticated.
	 *
	 * @return static
	 */
	public function assertGuest() {
		PHPUnit::assertEmpty(
			$this->current_user_info()['user_id'] ?? null,
			'The user is unexpectedly authenticated.'
		);

		return $this;
	}

	/**
	 * Assert that the user is authenticated as the given user.
	 *
	 * @param int|object $user User ID or object.
	 * @return static
	 */
	public function assertAuthenticatedAs( $user ) {
		$user_id = $user instanceof Core_Object ? $user->id() : $user;

		$expected = [
			'user_id' => $user_id,
		];

		PHPUnit::assertSame(
			$expected,
			$this->current_user_info(),
			'The currently authenticated user is not who was expected.'
		);

		return $this;
	}

	/**
	 * Determine if route() should use an absolute path.
	 *
	 * @return bool
	 */
	private function should_use_absolute_route_for_auth(): bool {
		return config( 'browser-testing.domain' ) !== null;
	}
}
