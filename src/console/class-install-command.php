<?php
/**
 * Install_Command class file.
 *
 * @package mantle-browser-testing
 * phpcs:disable WordPressVIPMinimum.Functions.RestrictedFunctions.directory_mkdir
 * phpcs:disable WordPressVIPMinimum.Functions.RestrictedFunctions
 */

namespace Mantle\Browser_Testing\Console;

use Mantle\Framework\Console\Command;

/**
 * Install Command
 */
class Install_Command extends Command {
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'browser-testing:install';

	/**
	 * Command Short Description.
	 *
	 * @var string
	 */
	protected $short_description = 'Install Browser Testing in the application.';

	/**
	 * Command Description.
	 *
	 * @var string
	 */
	protected $description = 'Install Browser Testing in the application.';

	/**
	 * Command synopsis.
	 *
	 * Supports registering command arguments in a string or array format.
	 * For example:
	 *
	 *     <argument> --example-flag
	 *
	 * @var string|array
	 */
	protected $synopsis = [
		[
			'description' => 'The proxy to download the binary through (example: "tcp://127.0.0.1:9000")',
			'name'        => 'proxy',
			'optional'    => true,
			'type'        => 'assoc',
		],
		[
			'description' => 'Bypass SSL certificate verification when installing through a proxy',
			'name'        => 'ssl-no-verify',
			'optional'    => true,
			'type'        => 'flag',
		],
	];

	/**
	 * Callback for the command.
	 *
	 * @param array $args Command Arguments.
	 * @param array $assoc_args Command flags.
	 */
	public function handle( array $args, array $assoc_args = [] ) {
		if ( ! is_dir( base_path( 'tests/browser/pages' ) ) ) {
			mkdir( base_path( 'tests/browser/pages' ), 0755, true );
		}

		if ( ! is_dir( base_path( 'tests/browser/components' ) ) ) {
			mkdir( base_path( 'tests/browser/components' ), 0755, true );
		}

		if ( ! is_dir( base_path( 'tests/browser/screenshots' ) ) ) {
			$this->create_screenshots_directory();
		}

		if ( ! is_dir( base_path( 'tests/browser/console' ) ) ) {
			$this->create_console_directory();
		}

		// todo: copy studs.

		$this->log( 'Browser Testing scaffolding installed successfully.' );

		$args = '--all';

		if ( $this->get_flag( 'proxy' ) ) {
			$args .= " --proxy={$this->get_flag( 'proxy' )}";
		}

		if ( $this->get_flag( 'ssl-no-verify' ) ) {
			$args .= ' --ssl-no-verify';
		}

		$this->call( "mantle browser-testing:chrome-driver {$args}" );

		$this->log( 'Installation complete.' );
	}

	/**
	 * Create the screenshots directory.
	 */
	protected function create_screenshots_directory() {
		mkdir( base_path( 'tests/browser/screenshots' ), 0755, true );

		file_put_contents(
			base_path( 'tests/browser/screenshots/.gitignore' ),
			$this->get_gitignore()
		);
	}

	/**
	 * Create the console directory.
	 *
	 * @return void
	 */
	protected function create_console_directory() {
		mkdir( base_path( 'tests/browser/console' ), 0755, true );

		file_put_contents(
			base_path( 'tests/browser/console/.gitignore' ),
			$this->get_gitignore()
		);
	}

	/**
	 * Retrieve a gitignore file that will ignore all contents in a directory
	 * except for the ignore file.
	 *
	 * @return string
	 */
	protected function get_gitignore(): string {
		return '*
!.gitignore';
	}
}
