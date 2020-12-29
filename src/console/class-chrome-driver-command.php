<?php
/**
 * Chrome_Driver_Command class file.
 *
 * @package mantle-browser-testing
 * phpcs:disable WordPressVIPMinimum.Functions.RestrictedFunctions
 * phpcs:disable WordPressVIPMinimum.Performance.FetchingRemoteData
 */

namespace Mantle\Browser_Testing\Console;

use Mantle\Browser_Testing\Operating_System;
use Mantle\Framework\Console\Command;
use Symfony\Component\Process\Process;
use ZipArchive;

/**
 * Install the Chrome Driver Command
 */
class Chrome_Driver_Command extends Command {
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'browser-testing:chrome-driver';

	/**
	 * Command Short Description.
	 *
	 * @var string
	 */
	protected $short_description = 'Install ChromeDriver binary.';

	/**
	 * Command Description.
	 *
	 * @var string
	 */
	protected $description = 'Install ChromeDriver binary.';

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
			'description' => 'Version',
			'name'        => 'version',
			'optional'    => true,
			'type'        => 'positional',
		],
		[
			'description' => 'Detect the installed Chrome / Chromium version',
			'name'        => 'detect',
			'optional'    => true,
			'type'        => 'flag',
		],
		[
			'description' => 'Flag to install a ChromeDriver for every OS',
			'name'        => 'all',
			'optional'    => true,
			'type'        => 'flag',
		],
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
	 * URL to the latest stable release version.
	 *
	 * @var string
	 */
	protected $latest_version_url = 'https://chromedriver.storage.googleapis.com/LATEST_RELEASE';

	/**
	 * URL to the latest release version for a major Chrome version.
	 *
	 * @var string
	 */
	protected $version_url = 'https://chromedriver.storage.googleapis.com/LATEST_RELEASE_%d';

	/**
	 * URL to the ChromeDriver download.
	 *
	 * @var string
	 */
	protected $download_url = 'https://chromedriver.storage.googleapis.com/%s/chromedriver_%s.zip';

	/**
	 * Download slugs for the available operating systems.
	 *
	 * @var array
	 */
	protected $slugs = [
		'linux' => 'linux64',
		'mac'   => 'mac64',
		'win'   => 'win32',
	];

	/**
	 * The legacy versions for the ChromeDriver.
	 *
	 * @var array
	 */
	protected $legacy_versions = [
		43 => '2.20',
		44 => '2.20',
		45 => '2.20',
		46 => '2.21',
		47 => '2.21',
		48 => '2.21',
		49 => '2.22',
		50 => '2.22',
		51 => '2.23',
		52 => '2.24',
		53 => '2.26',
		54 => '2.27',
		55 => '2.28',
		56 => '2.29',
		57 => '2.29',
		58 => '2.31',
		59 => '2.32',
		60 => '2.33',
		61 => '2.34',
		62 => '2.35',
		63 => '2.36',
		64 => '2.37',
		65 => '2.38',
		66 => '2.40',
		67 => '2.41',
		68 => '2.42',
		69 => '2.44',
	];

	/**
	 * Path to the bin directory.
	 *
	 * @var string
	 */
	protected $directory = __DIR__ . '/../../bin/';

	/**
	 * The default commands to detect the installed Chrome / Chromium version.
	 *
	 * @var array
	 */
	protected $chrome_version_commands = [
		'linux' => [
			'/usr/bin/google-chrome --version',
			'/usr/bin/chromium-browser --version',
			'/usr/bin/google-chrome-stable --version',
		],
		'mac'   => [
			'/Applications/Google\ Chrome.app/Contents/MacOS/Google\ Chrome --version',
		],
		'win'   => [
			'reg query "HKEY_CURRENT_USER\Software\Google\Chrome\BLBeacon" /v version',
		],
	];

	/**
	 * Callback for the command.
	 *
	 * @param array $args Command Arguments.
	 * @param array $assoc_args Command flags.
	 */
	public function handle( array $args, array $assoc_args = [] ) {
		$version = $this->version();
		$all     = $this->option( 'all' );

		$current_os = Operating_System::id();

		foreach ( $this->slugs as $os => $slug ) {
			if ( $all || ( $os === $current_os ) ) {
				$archive = $this->download( $version, $slug );

				$binary = $this->extract( $archive );

				$this->rename( $binary, $os );
			}
		}

		$message = 'ChromeDriver %s successfully installed for version %s.';

		$this->log(
			sprintf(
				$message,
				$all ? 'binaries' : 'binary',
				$version
			)
		);
	}

	/**
	 * Get the desired ChromeDriver version.
	 *
	 * @return string
	 */
	protected function version(): string {
		$version = $this->argument( 'version' );

		if ( $this->option( 'detect' ) ) {
			$version = $this->detect_chrome_version( Operating_System::id() );
		}

		if ( ! $version ) {
			return $this->latest_version();
		}

		if ( ! ctype_digit( $version ) ) {
			return $version;
		}

		$version = (int) $version;

		if ( $version < 70 ) {
			return $this->legacy_versions[ $version ];
		}

		return trim(
			$this->get_url(
				sprintf( $this->version_url, $version )
			)
		);
	}

	/**
	 * Detect the installed Chrome / Chromium major version.
	 *
	 * @param string $os Operating System.
	 * @return int|bool
	 */
	protected function detect_chrome_version( string $os ) {
		foreach ( $this->chrome_version_commands[ $os ] as $command ) {
			$process = Process::fromShellCommandline( $command );

			$process->run();

			preg_match( '/(\d+)(\.\d+){3}/', $process->getOutput(), $matches );

			if ( ! isset( $matches[1] ) ) {
				continue;
			}

			return $matches[1];
		}

		$this->error( 'Chrome version could not be detected.', false );

		return false;
	}

	/**
	 * Get the latest stable ChromeDriver version.
	 *
	 * @return string
	 */
	protected function latest_version() {
		$stream_options = [];

		if ( $this->option( 'ssl-no-verify' ) ) {
			$stream_options = [
				'ssl' => [
					'verify_peer_name' => false,
					'verify_peer'      => false,
				],
			];
		}

		if ( $this->option( 'proxy' ) ) {
			$stream_options['http'] = [
				'proxy'           => $this->option( 'proxy' ),
				'request_fulluri' => true,
			];
		}

		return trim( file_get_contents( $this->latest_version_url, false, stream_context_create( $stream_options ) ) );
	}

	/**
	 * Download the ChromeDriver archive.
	 *
	 * @param  string $version Version to download.
	 * @param  string $slug Slug of the file.
	 * @return string
	 */
	protected function download( $version, $slug ) {
		$url     = sprintf( $this->download_url, $version, $slug );
		$archive = $this->directory . 'chromedriver.zip';

		file_put_contents( $archive, $this->get_url( $url ) );

		return $archive;
	}

	/**
	 * Extract the ChromeDriver binary from the archive and delete the archive.
	 *
	 * @param  string $archive Archive to extract.
	 * @return string
	 */
	protected function extract( $archive ) {
		$zip = new ZipArchive();

		$zip->open( $archive );

		$zip->extractTo( $this->directory );

		$binary = $zip->getNameIndex( 0 );

		$zip->close();

		unlink( $archive );

		return $binary;
	}

	/**
	 * Rename the ChromeDriver binary and make it executable.
	 *
	 * @param  string $binary Binary to rename.
	 * @param  string $os Operating system name.
	 * @return void
	 */
	protected function rename( $binary, $os ) {
		$new_name = str_replace( 'chromedriver', 'chromedriver-' . $os, $binary );

		rename( $this->directory . $binary, $this->directory . $new_name );

		chmod( $this->directory . $new_name, 0755 );
	}

	/**
	 * Get the contents of a URL using the 'proxy' and 'ssl-no-verify' command options.
	 *
	 * @param  string $url URL to fetch from.
	 * @return string|bool
	 */
	protected function get_url( string $url ) {
		$options = [];

		if ( $this->option( 'proxy' ) ) {
			$options['http'] = [
				'proxy'           => $this->option( 'proxy' ),
				'request_fulluri' => true,
			];
		}

		if ( $this->option( 'ssl-no-verify' ) ) {
			$options['ssl'] = [ 'verify_peer' => false ];
		}

		$context = stream_context_create( $options );

		return file_get_contents( $url, false, $context );
	}
}
