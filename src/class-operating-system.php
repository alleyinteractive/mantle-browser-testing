<?php
/**
 * Operating_System class file.
 *
 * @package mantle-browser-testing
 */

namespace Mantle\Browser_Testing;

use Mantle\Support\Str;

/**
 * Operating System Utility Class
 */
class Operating_System {

	/**
	 * Returns the current OS identifier.
	 *
	 * @return string
	 */
	public static function id(): string {
		return static::on_windows() ? 'win' : ( static::on_mac() ? 'mac' : 'linux' );
	}

	/**
	 * Determine if the operating system is Windows or Windows Subsystem for Linux.
	 *
	 * @return bool
	 */
	public static function on_windows(): bool {
		return PHP_OS === 'WINNT' || Str::contains( php_uname(), 'Microsoft' );
	}

	/**
	 * Determine if the operating system is macOS.
	 *
	 * @return bool
	 */
	public static function on_mac(): bool {
			return PHP_OS === 'Darwin';
	}
}
