<?php
/**
 * Operating_System class file.
 *
 * @package mantle-browser-testing
 */

namespace Mantle\Browser_Testing;

use InvalidArgumentException;
use Mantle\Support\Str;

/**
 * Operating System Utility Class
 */
class Operating_System {
	/**
	 * List of available operating system platforms.
	 *
	 * @var array<string, array{slug: string, commands: array<int, string>}>
	 */
	protected static $platforms = [
		'linux'     => [
			'slug'     => 'linux64',
			'commands' => [
				'/usr/bin/google-chrome --version',
				'/usr/bin/chromium-browser --version',
				'/usr/bin/chromium --version',
				'/usr/bin/google-chrome-stable --version',
			],
		],
		'mac'       => [
			'slug'     => 'mac-x64',
			'commands' => [
				'/Applications/Google\ Chrome\ for\ Testing.app/Contents/MacOS/Google\ Chrome\ for\ Testing --version',
				'/Applications/Google\ Chrome.app/Contents/MacOS/Google\ Chrome --version',
			],
		],
		'mac-intel' => [
			'slug'     => 'mac-x64',
			'commands' => [
				'/Applications/Google\ Chrome\ for\ Testing.app/Contents/MacOS/Google\ Chrome\ for\ Testing --version',
				'/Applications/Google\ Chrome.app/Contents/MacOS/Google\ Chrome --version',
			],
		],
		'mac-arm'   => [
			'slug'     => 'mac-arm64',
			'commands' => [
				'/Applications/Google\ Chrome\ for\ Testing.app/Contents/MacOS/Google\ Chrome\ for\ Testing --version',
				'/Applications/Google\ Chrome.app/Contents/MacOS/Google\ Chrome --version',
			],
		],
		'win'       => [
			'slug'     => 'win32',
			'commands' => [
				'reg query "HKEY_CURRENT_USER\Software\Google\Chrome\BLBeacon" /v version',
			],
		],
	];

	/**
	 * Resolve the Chrome version commands for the given operating system.
	 *
	 * @param  string $os
	 * @return array<int, string>
	 */
	public static function chrome_version_commands( string $os ): array {
		$commands = static::$platforms[ $os ]['commands'] ?? null;

		if ( is_null( $commands ) ) {
			throw new InvalidArgumentException( "Unable to find commands for Operating System [{$os}]" );
		}

		return $commands;
	}

	/**
	 * Resolve the ChromeDriver slug for the given operating system.
	 *
	 * @param  string      $os Operating system name.
	 * @param  string|null $version Chrome version.
	 * @return string
	 */
	public static function chrome_driver_slug( string $os, ?string $version = null ): string {
		$slug = static::$platforms[ $os ]['slug'] ?? null;

		if ( is_null( $slug ) ) {
			throw new InvalidArgumentException( "Unable to find ChromeDriver slug for Operating System [{$os}]" );
		}

		if ( ! is_null( $version ) && version_compare( $version, '115.0', '<' ) ) {
			if ( $slug === 'mac-arm64' ) {
				return version_compare( $version, '106.0.5249', '<' ) ? 'mac64_m1' : 'mac_arm64';
			} elseif ( $slug === 'mac-x64' ) {
				return 'mac64';
			}
		}

		return $slug;
	}

	/**
	 * Get all supported operating systems.
	 *
	 * @return array<int, string>
	 */
	public static function all(): array {
		return array_keys( static::$platforms );
	}

	/**
	 * Returns the current OS identifier.
	 *
	 * @return string
	 */
	public static function id(): string {
		if ( static::on_windows() ) {
			return 'win';
		} elseif ( static::on_mac() ) {
			return static::mac_architecture_id();
		}

		return 'linux';
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

	/**
	 * Get the current macOS platform architecture.
	 *
	 * @return string
	 */
	public static function mac_architecture_id(): string {
		return match ( php_uname( 'm' ) ) {
			'arm64' => 'mac-arm',
			'x86_64' => 'mac-intel',
			default => 'mac',
		};
	}
}
