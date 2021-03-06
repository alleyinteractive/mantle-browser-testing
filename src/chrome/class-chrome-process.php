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
	 * The path to the Chromedriver.
	 *
	 * @var string
	 */
	protected $driver;

	/**
	 * Create a new ChromeProcess instance.
	 *
	 * @param string $driver Chrome driver path.
	 * @return void
	 *
	 * @throws RuntimeException Thrown on invalid driver path.
	 */
	public function __construct( $driver = null ) {
		$this->driver = $driver;

		if ( ! is_null( $driver ) && realpath( $driver ) === false ) {
			throw new RuntimeException( "Invalid path to Chromedriver [{$driver}]." );
		}
	}

	/**
	 * Build the process to run Chromedriver.
	 *
	 * @param array $arguments Arguments to pass.
	 * @return Process
	 */
	public function to_process( array $arguments = [] ): Process {
		if ( $this->driver ) {
			return $this->process( $arguments );
		}

		if ( $this->on_windows() ) {
			$this->driver = realpath( __DIR__ . '/../../bin/chromedriver-win.exe' );
		} elseif ( $this->on_mac() ) {
			$this->driver = realpath( __DIR__ . '/../../bin/chromedriver-mac' );
		} else {
			$this->driver = realpath( __DIR__ . '/../../bin/chromedriver-linux' );
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

		return [ 'DISPLAY' => $_ENV['DISPLAY'] ?? ':0' ];
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
}
