<?php
/**
 * Chrome_Driver_Command class file.
 *
 * @package mantle-browser-testing
 * phpcs:disable WordPressVIPMinimum.Functions.RestrictedFunctions
 * phpcs:disable WordPressVIPMinimum.Performance.FetchingRemoteData
 */

namespace Mantle\Browser_Testing\Console;

use Exception;
use Mantle\Browser_Testing\Operating_System;
use Mantle\Console\Command;
use Mantle\Support\Str;
use Symfony\Component\Process\Process;
use ZipArchive;

/**
 * Command to install the Chrome Driver
 */
class Chrome_Driver_Command extends Command {
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $signature = 'browser-testing:chrome-driver {version?} {--detect} {--all} {--proxy=} {--ssl-no-verify}';

	/**
	 * Command Description.
	 *
	 * @var string
	 */
	protected $description = 'Install ChromeDriver binary.';

	/**
	 * The legacy versions for the ChromeDriver.
	 *
	 * @var array
	 */
	public array $legacy_versions = [
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
	 * Callback for the command.
	 */
	public function handle() {
		$this->directory = Str::finish( realpath( $this->directory ), DIRECTORY_SEPARATOR );

		if ( ! $this->directory ) {
			$this->error( 'Could not resolve the bin directory.' );
		}

		$version = $this->version();
		$all     = $this->option( 'all' );

		$current_os = Operating_System::id();

		foreach ( Operating_System::all() as $os ) {
			if ( $all || ( $os === $current_os ) ) {
				$archive = $this->download( $version, $os );

				$binary = $this->extract( $version, $archive );

				$this->rename( $binary, $os );
			}
		}

		$message = 'ChromeDriver %s successfully installed for version %s.';

		$this->line(
			sprintf(
				$message,
				$all ? 'binaries' : 'binary',
				$this->colorize( $version, 'yellow' )
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
		} elseif ( $version < 115 ) {
			return $this->fetch_chrome_version_from_url( $version );
		}

		$milestones = $this->resolve_chrome_version_per_milestone();

		return $milestones['milestones'][ $version ]['version']
			?? throw new Exception( 'Could not determine the ChromeDriver version.' );
	}

	/**
	 * Detect the installed Chrome / Chromium major version.
	 *
	 * @param string $os Operating System.
	 * @return string|bool
	 */
	protected function detect_chrome_version( string $os ) {
		foreach ( Operating_System::chrome_version_commands( $os ) as $command ) {
			$process = Process::fromShellCommandline( $command );

			$process->run();

			preg_match( '/(\d+)(\.\d+){3}/', $process->getOutput(), $matches );

			if ( ! isset( $matches[1] ) ) {
				continue;
			}

			return $matches[1];
		}

		$this->error( 'Chrome version could not be detected.' );

		return false;
	}

	/**
	 * Get the latest stable ChromeDriver version.
	 *
	 * @throws \Exception If the version could not be determined.
	 *
	 * @return string
	 */
	protected function latest_version(): string {
		$versions = json_decode(
			$this->get_url( 'https://googlechromelabs.github.io/chrome-for-testing/last-known-good-versions-with-downloads.json' ),
			true,
		);

		return $versions['channels']['Stable']['version']
			?? throw new Exception( 'Could not get the latest ChromeDriver version.' );
	}

	/**
	 * Download the ChromeDriver archive.
	 *
	 * @param  string $version Version to download.
	 * @param  string $slug Slug of the file.
	 * @return string
	 */
	protected function download( string $version, string $slug ): string {
		$url = $this->resolve_chrome_driver_download_url( $version, $slug );

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
	protected function extract( $version, $archive ) {
		$zip = new ZipArchive();

		$zip->open( $archive );

		$zip->extractTo( $this->directory );

		$binary = $zip->getNameIndex(version_compare($version, '115.0', '<') ? 0 : 1);

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
		$binary = str_replace( DIRECTORY_SEPARATOR, '/', $binary );

		$new_name = Str::contains( $binary, '/' )
			? Str::after( str_replace( 'chromedriver', 'chromedriver-' . $os, $binary ), '/' )
			: str_replace( 'chromedriver', 'chromedriver-' . $os, $binary );

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

	/**
	 * Get the Chrome version from URL.
	 *
	 * @param int $version Version to fetch.
	 * @return string
	 */
	protected function fetch_chrome_version_from_url( int $version ) {
		return trim(
			(string) $this->get_url(
				sprintf( 'https://chromedriver.storage.googleapis.com/LATEST_RELEASE_%d', $version )
			)
		);
	}

	/**
	 * Get the Chrome versions per milestone.
	 *
	 * @return array
	 */
	protected function resolve_chrome_version_per_milestone(): array {
		return json_decode(
			$this->get_url( 'https://googlechromelabs.github.io/chrome-for-testing/latest-versions-per-milestone-with-downloads.json' ),
			true
		);
	}

	/**
	 * Resolve the download URL.
	 *
	 * @param string $version Version to resolve.
	 * @param string $os Operating system name.
	 * @return string
	 *
	 * @throws \Exception If the version could not be determined.
	 * @throws \Exception If the ChromeDriver version could not be determined.
	 */
	protected function resolve_chrome_driver_download_url( string $version, string $os ): string {
		$slug = Operating_System::chrome_driver_slug( $os, $version );

		if ( version_compare( $version, '115.0', '<' ) ) {
			return sprintf( 'https://chromedriver.storage.googleapis.com/%s/chromedriver_%s.zip', $version, $slug );
		}

		$milestone = (int) $version;

		$versions = $this->resolve_chrome_version_per_milestone();

		/** @var array<string, mixed> $chromedrivers */
		$chromedrivers = $versions['milestones'][ $milestone ]['downloads']['chromedriver']
			?? throw new Exception( 'Could not get the ChromeDriver version.' );

		return collect( $chromedrivers )->firstWhere( 'platform', $slug )['url']
			?? throw new Exception( 'Could not get the ChromeDriver version.' );
	}
}
