<?php
/**
 * Supports_Chrome trait file.
 *
 * @package mantle-browser-testing
 */

namespace Mantle\Browser_Testing\Chrome;

use Symfony\Component\Process\Process;

/**
 * Concern for interactions with Chrome.
 *
 * @mixin \Mantle\Browser_Testing\Test_Case
 */
trait Supports_Chrome {

	/**
	 * The path to the custom Chromedriver binary.
	 *
	 * @var string|null
	 */
	protected static ?string $chrome_driver = null;

	/**
	 * The Chromedriver process instance.
	 *
	 * @var \Symfony\Component\Process\Process
	 */
	protected static Process $chrome_process;

	/**
	 * Start the Chromedriver process.
	 *
	 * @param  array $arguments Arguments for the driver.
	 * @return void
	 */
	public static function start_chrome_driver( array $arguments = [] ) {
		static::$chrome_process = static::build_chrome_process( $arguments );

		static::$chrome_process->start();

		static::afterClass(
			fn () => static::stop_chrome_driver(),
		);
	}

	/**
	 * Stop the Chromedriver process.
	 *
	 * @return void
	 */
	public static function stop_chrome_driver() {
		if ( static::$chrome_process ) {
			static::$chrome_process->stop();
		}
	}

	/**
	 * Build the process to run the Chromedriver.
	 *
	 * @param  array $arguments Arguments for the driver.
	 * @return \Symfony\Component\Process\Process
	 */
	protected static function build_chrome_process( array $arguments = [] ) {
		return ( new Chrome_Process( static::$chrome_driver ) )->to_process( $arguments );
	}

	/**
	 * Set the path to the custom Chromedriver.
	 *
	 * @param  string $path Path to use.
	 * @return void
	 */
	public static function use_chrome_driver( $path ) {
		static::$chrome_driver = $path;
	}
}
