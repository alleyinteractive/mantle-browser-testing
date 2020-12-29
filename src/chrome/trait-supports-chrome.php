<?php

namespace Mantle\Browser_Testing\Chrome;

trait Supports_Chrome {

	/**
	 * The path to the custom Chromedriver binary.
	 *
	 * @var string|null
	 */
	protected static $chrome_driver;

	/**
	 * The Chromedriver process instance.
	 *
	 * @var \Symfony\Component\Process\Process
	 */
	protected static $chrome_process;

	/**
	 * Start the Chromedriver process.
	 *
	 * @param  array $arguments
	 * @return void
	 *
	 * @throws \RuntimeException
	 */
	public static function start_chrome_driver( array $arguments = [] ) {
		static::$chrome_process = static::build_chrome_process( $arguments );

		static::$chrome_process->start();

		static::afterClass(
			function () {
				static::stop_chrome_driver();
			}
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
	 * @param  array $arguments
	 * @return \Symfony\Component\Process\Process
	 *
	 * @throws \RuntimeException
	 */
	protected static function build_chrome_process( array $arguments = [] ) {
		return ( new Chrome_Process( static::$chrome_driver ) )->to_process( $arguments );
	}

	/**
	 * Set the path to the custom Chromedriver.
	 *
	 * @param  string $path
	 * @return void
	 */
	public static function use_chrome_driver( $path ) {
		static::$chrome_driver = $path;
	}
}
