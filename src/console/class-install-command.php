<?php
/**
 * Install_Command class file.
 *
 * @package mantle-browser-testing
 * phpcs:disable WordPressVIPMinimum.Functions.RestrictedFunctions.directory_mkdir
 * phpcs:disable WordPressVIPMinimum.Functions.RestrictedFunctions
 */

namespace Mantle\Browser_Testing\Console;

use Mantle\Console\Command;

/**
 * Install Command
 */
class Install_Command extends Command {
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $signature = 'browser-testing:install {--proxy=} {--ssl-no-verify}';

	/**
	 * Command Short Description.
	 *
	 * @var string
	 */
	protected $description = 'Install Browser Testing in the application.';

	/**
	 * Callback for the command.
	 */
	public function handle() {
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

		$stubs = [
			'class-browser-test-case.stub' => base_path( 'tests/browser/class-browser-test-case.php' ),
			'class-page.stub'              => base_path( 'tests/browser/class-page.php' ),
			'class-home-page.stub'         => base_path( 'tests/browser/class-home-page.php' ),
		];

		foreach ( $stubs as $stub => $location ) {
			if ( ! is_file( $location ) ) {
				copy( __DIR__ . '/../stubs/' . $stub, $location );
			}
		}

		$this->line( 'Browser Testing scaffolding installed successfully.' );

		$args = [
			'--all',
		];

		if ( $this->option( 'proxy' ) ) {
			$args[] = "--proxy={$this->option( 'proxy' )}";
		}

		if ( $this->option( 'ssl-no-verify' ) ) {
			$args[] = '--ssl-no-verify';
		}

		$this->call( 'mantle browser-testing:chrome-driver', $args );

		// Generate an example test case.
		$this->call( 'mantle make:browser-testing', [ 'name' => 'Example' ] );

		$this->success( 'Installation complete!' );
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
