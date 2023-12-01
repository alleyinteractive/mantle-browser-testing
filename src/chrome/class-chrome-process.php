<?php
/**
 * Chrome_Process class file.
 *
 * @package mantle-browser-testing
 */

namespace Mantle\Browser_Testing\Chrome;

use Mantle\Browser_Testing\Operating_System;
use RuntimeException;
use Symfony\Component\Process\Process;

/**
 * Package to manage the Chrome Process
 */
class Chrome_Process {
	/**
	 * Create a new ChromeProcess instance.
	 *
	 * @param string|null $driver Chrome driver path.
	 * @return void
	 *
	 * @throws RuntimeException Thrown on invalid driver path.
	 */
	public function __construct( protected ?string $driver = null ) {}

	/**
	 * Build the process to run Chromedriver.
	 *
	 * @param array $arguments Arguments to pass.
	 * @return Process
	 */
	public function to_process( array $arguments = [] ): Process {
		if ( $this->driver ) {
			$driver = $this->driver;
		} else {
			$filenames = [
				'linux'     => 'chromedriver-linux',
				'mac'       => 'chromedriver-mac',
				'mac-intel' => 'chromedriver-mac-intel',
				'mac-arm'   => 'chromedriver-mac-arm',
				'win'       => 'chromedriver-win.exe',
			];

			$driver = __DIR__ . '/../../bin' . DIRECTORY_SEPARATOR . $filenames[ $this->operating_system_id() ];
		}

		$this->driver = realpath( $driver );

		dd('driver', $this->driver);

		if ( ! $this->driver ) {
			throw new RuntimeException(
				"Invalid path to Chromedriver [{$driver}]. Make sure to install the Chromedriver first by running the dusk:chrome-driver command."
			);
		}

		return $this->process( $arguments );
	}

	/**
	 * Build the Chromedriver with Symfony Process.
	 *
	 * @param array $arguments Arguments to pass.
	 * @return Process
	 */
	protected function process( array $arguments = [] ): Process {
		return new Process(
			array_merge( [ realpath( $this->driver ) ], $arguments ),
			null,
			$this->chrome_environment()
		);
	}

	/**
	 * Get the Chromedriver environment variables.
	 *
	 * @return array
	 */
	protected function chrome_environment(): array {
		if ( $this->on_mac() || $this->on_windows() ) {
			return [];
		}

		return [ 'DISPLAY' => $_ENV['DISPLAY'] ?? ':0' ]; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	}

	/**
	 * Determine if Dusk is running on Windows or Windows Subsystem for Linux.
	 *
	 * @return bool
	 */
	protected function on_windows(): bool {
		return Operating_System::on_windows();
	}

	/**
	 * Determine if Dusk is running on Mac.
	 *
	 * @return bool
	 */
	protected function on_mac(): bool {
		return Operating_System::on_mac();
	}

	/**
	 * Determine OS ID.
	 *
	 * @return string
	 */
	protected function operating_system_id(): string {
		return Operating_System::id();
	}
}
