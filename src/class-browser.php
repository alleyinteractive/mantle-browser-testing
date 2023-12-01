<?php
/**
 * Browser class file.
 *
 * @package mantle-browser-testing
 * phpcs:disable WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceAfterOpenParenthesis
 * phpcs:disable WordPressVIPMinimum.Functions.RestrictedFunctions
 */

namespace Mantle\Browser_Testing;

use BadMethodCallException;
use Closure;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\WebDriverBrowserType;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverDimension;
use Facebook\WebDriver\WebDriverPoint;
use Mantle\Support\Str;
use Mantle\Support\Traits\Macroable;

/**
 * Browser Package
 */
class Browser {
	use Concerns\Interacts_With_Authentication,
		Concerns\Interacts_With_Cookies,
		Concerns\Interacts_With_Elements,
		Concerns\Interacts_With_Javascript,
		Concerns\Interacts_With_Mouse,
		Concerns\Makes_Assertions,
		Concerns\Makes_Url_Assertions,
		Concerns\Waits_For_Elements,
		Macroable {
			__call as macro_call;
	}

	/**
	 * The base URL for all URLs.
	 *
	 * @var string
	 */
	public static $base_url;

	/**
	 * The directory that will contain any screenshots.
	 *
	 * @var string
	 */
	public static $store_screenshots_at;

	/**
	 * The directory that will contain any console logs.
	 *
	 * @var string
	 */
	public static $store_console_log_at;

	/**
	 * The directory where source code snapshots will be stored.
	 *
	 * @var string
	 */
	public static $store_source_at;

	/**
	 * The browsers that support retrieving logs.
	 *
	 * @var array
	 */
	public static $supports_remote_logs = [
		WebDriverBrowserType::CHROME,
		WebDriverBrowserType::PHANTOMJS,
	];

	/**
	 * Get the callback which resolves the default user to authenticate.
	 *
	 * @var \Closure
	 */
	public static $user_resolver;

	/**
	 * The default wait time in seconds.
	 *
	 * @var int
	 */
	public static $wait_seconds = 5;

	/**
	 * The RemoteWebDriver instance.
	 *
	 * @var \Facebook\WebDriver\Remote\RemoteWebDriver
	 */
	public $driver;

	/**
	 * The element resolver instance.
	 *
	 * @var Element_Resolver
	 */
	public $resolver;

	/**
	 * The page object currently being viewed.
	 *
	 * @var mixed
	 */
	public $page;

	/**
	 * The component object currently being viewed.
	 *
	 * @var mixed
	 */
	public $component;

	/**
	 * Indicates that the browser should be resized to fit the entire "body" before screenshotting failures.
	 *
	 * @var bool
	 */
	public $fit_on_failure = true;

	/**
	 * Create a browser instance.
	 *
	 * @param RemoteWebDriver  $driver Driver instance.
	 * @param Element_Resolver $resolver Resolver instance.
	 */
	public function __construct( $driver, $resolver = null ) {
		$this->driver = $driver;

		$this->resolver = $resolver ?: new Element_Resolver( $driver );
	}

	/**
	 * Browse to the given URL.
	 *
	 * @param string|Page $url URL or page to visit.
	 * @return static
	 */
	public function visit( $url ) {
		// First, if the URL is an object it means we are actually dealing with a page
		// and we need to create this page then get the URL from the page object as
		// it contains the URL. Once that is done, we will be ready to format it.
		if ( is_object( $url ) ) {
			$page = $url;

			$url = $page->url();
		}

		// If the URL does not start with http or https, then we will prepend the base
		// URL onto the URL and navigate to the URL. This will actually navigate to
		// the URL in the browser. Then we will be ready to make assertions, etc.
		if ( ! Str::starts_with( $url, [ 'http://', 'https://' ] ) ) {
			$url = static::$base_url . '/' . ltrim( $url, '/' );
		}

		$this->driver->navigate()->to( $url );

		// If the page variable was set, we will call the "on" method which will set a
		// page instance variable and call an assert method on the page so that the
		// page can have the chance to verify that we are within the right pages.
		if ( isset( $page ) ) {
			$this->on( $page );
		}

		return $this;
	}

	/**
	 * Browse to the given route.
	 *
	 * @param string $route Route to visit.
	 * @param array  $parameters Parameters for the route.
	 * @return static
	 */
	public function visit_route( $route, $parameters = [] ) {
		return $this->visit( route( $route, $parameters ) );
	}

	/**
	 * Browse to the "about:blank" page.
	 *
	 * @return static
	 */
	public function blank() {
		$this->driver->navigate()->to( 'about:blank' );

		return $this;
	}

	/**
	 * Set the current page object.
	 *
	 * @param mixed $page Page instance.
	 * @return static
	 */
	public function on( $page ) {
		$this->on_without_assert( $page );

		$page->assert( $this );

		return $this;
	}

	/**
	 * Set the current page object without executing the assertions.
	 *
	 * @param mixed $page Page instance.
	 * @return static
	 */
	public function on_without_assert( $page ) {
		$this->page = $page;

		// Here we will set the page elements on the resolver instance, which will allow
		// the developer to access short-cuts for CSS selectors on the page which can
		// allow for more expressive navigation and interaction with all the pages.
		$this->resolver->pageElements(
			array_merge(
				$page::site_elements(),
				$page->elements()
			)
		);

		return $this;
	}

	/**
	 * Refresh the page.
	 *
	 * @return static
	 */
	public function refresh() {
		$this->driver->navigate()->refresh();

		return $this;
	}

	/**
	 * Navigate to the previous page.
	 *
	 * @return static
	 */
	public function back() {
		$this->driver->navigate()->back();

		return $this;
	}

	/**
	 * Navigate to the next page.
	 *
	 * @return static
	 */
	public function forward() {
		$this->driver->navigate()->forward();

		return $this;
	}

	/**
	 * Maximize the browser window.
	 *
	 * @return static
	 */
	public function maximize() {
		$this->driver->manage()->window()->maximize();

		return $this;
	}

	/**
	 * Resize the browser window.
	 *
	 * @param int $width Width to set.
	 * @param int $height Height to set.
	 * @return static
	 */
	public function resize( $width, $height ) {
		$this->driver->manage()->window()->setSize(
			new WebDriverDimension( $width, $height )
		);

		return $this;
	}

	/**
	 * Make the browser window as large as the content.
	 *
	 * @return static
	 */
	public function fit_content() {
		$this->driver->switchTo()->defaultContent();

		$html = $this->driver->findElement( WebDriverBy::tagName( 'html' ) );

		if ( ! empty( $html ) && $html->getSize()->getWidth() >= 0 && $html->getSize()->getHeight() >= 0 ) {
			$this->resize( $html->getSize()->getWidth(), $html->getSize()->getHeight() );
		}

		return $this;
	}

	/**
	 * Disable fit on failures.
	 *
	 * @return static
	 */
	public function disable_fit_on_failure() {
		$this->fit_on_failure = false;

		return $this;
	}

	/**
	 * Enable fit on failures.
	 *
	 * @return static
	 */
	public function enable_fit_on_failure() {
		$this->fit_on_failure = true;

		return $this;
	}

	/**
	 * Move the browser window.
	 *
	 * @param  int $x X coordinate.
	 * @param  int $y Y coordinate.
	 * @return static
	 */
	public function move( $x, $y ) {
		$this->driver->manage()->window()->setPosition(
			new WebDriverPoint( $x, $y )
		);

		return $this;
	}

	/**
	 * Scroll element into view at the given selector.
	 *
	 * @param  string $selector Selector to scroll to.
	 * @return static
	 */
	public function scrollIntoView( $selector ) {
		$selector = addslashes( $this->resolver->format( $selector ) );

		$this->driver->executeScript( "document.querySelector(\"$selector\").scrollIntoView();" );

		return $this;
	}

	/**
	 * Scroll screen to element at the given selector.
	 *
	 * @param  string $selector Selector to scroll to.
	 * @return static
	 */
	public function scrollTo( $selector ) {
		$this->ensurejQueryIsAvailable();

		$selector = addslashes( $this->resolver->format( $selector ) );

		$this->driver->executeScript( "jQuery(\"html, body\").animate({scrollTop: jQuery(\"$selector\").offset().top}, 0);" );

		return $this;
	}

	/**
	 * Take a screenshot and store it with the given name.
	 *
	 * @param  string $name Screenshot name.
	 * @return static
	 */
	public function screenshot( $name ) {
		$file_path = sprintf( '%s/%s.png', rtrim( static::$store_screenshots_at, '/' ), $name );

		$directory_path = dirname( $file_path );

		if ( ! is_dir( $directory_path ) ) {
			mkdir( $directory_path, 0777, true );
		}

		$this->driver->takeScreenshot( $file_path );

		return $this;
	}

	/**
	 * Store the console output with the given name.
	 *
	 * @param  string $name File name.
	 * @return static
	 */
	public function storeConsoleLog( $name ) {
		if ( in_array( $this->driver->getCapabilities()->getBrowserName(), static::$supports_remote_logs ) ) {
			$console = $this->driver->manage()->getLog( 'browser' );

			if ( ! empty( $console ) ) {
				file_put_contents(
					sprintf( '%s/%s.log', rtrim( static::$store_console_log_at, '/' ), $name ),
					wp_json_encode( $console, JSON_PRETTY_PRINT )
				);
			}
		}

		return $this;
	}

	/**
	 * Store a snapshot of the page's current source code with the given name.
	 *
	 * @param  string $name File name.
	 * @return static
	 */
	public function storeSource( $name ) {
		$source = $this->driver->getPageSource();

		if ( ! empty( $source ) ) {
			file_put_contents(
				sprintf( '%s/%s.txt', rtrim( static::$store_source_at, '/' ), $name ),
				$source
			);
		}

		return $this;
	}

	/**
	 * Switch to a specified frame in the browser and execute the given callback.
	 *
	 * @param  string   $selector Selector to switch to.
	 * @param  \Closure $callback Callback to invoke.
	 * @return static
	 */
	public function withinFrame( $selector, Closure $callback ) {
		$this->driver->switchTo()->frame( $this->resolver->findOrFail( $selector ) );

		$callback( $this );

		$this->driver->switchTo()->defaultContent();

		return $this;
	}

	/**
	 * Execute a Closure with a scoped browser instance.
	 *
	 * @param  string   $selector Selector to use.
	 * @param  \Closure $callback Callback to invoke.
	 * @return static
	 */
	public function within( $selector, Closure $callback ) {
		return $this->with( $selector, $callback );
	}

	/**
	 * Execute a Closure with a scoped browser instance.
	 *
	 * @param  string   $selector Selector to use.
	 * @param  \Closure $callback Callback to invoke.
	 * @return static
	 */
	public function with( $selector, Closure $callback ) {
		$browser = new static(
			$this->driver,
			new Element_Resolver( $this->driver, $this->resolver->format( $selector ) )
		);

		if ( $this->page ) {
			$browser->on_without_assert( $this->page );
		}

		if ( $selector instanceof Component ) {
			$browser->on_component( $selector, $this->resolver );
		}

		call_user_func( $callback, $browser );

		return $this;
	}

	/**
	 * Execute a Closure outside of the current browser scope.
	 *
	 * @param  string   $selector Selector to use.
	 * @param  \Closure $callback Callback to invoke.
	 * @return static
	 */
	public function elsewhere( $selector, Closure $callback ) {
		$browser = new static(
			$this->driver,
			new Element_Resolver( $this->driver, 'body ' . $selector )
		);

		if ( $this->page ) {
			$browser->on_without_assert( $this->page );
		}

		if ( $selector instanceof Component ) {
			$browser->on_component( $selector, $this->resolver );
		}

		call_user_func( $callback, $browser );

		return $this;
	}

	/**
	 * Execute a Closure outside of the current browser scope when the selector is available.
	 *
	 * @param  string   $selector Selector to use.
	 * @param  \Closure $callback Callback to invoke.
	 * @return static
	 */
	public function elsewhereWhenAvailable( $selector, Closure $callback ) {
		return $this->elsewhere(
			'',
			function ( $browser ) use ( $selector, $callback ) {
				$browser->whenAvailable( $selector, $callback );
			}
		);
	}

	/**
	 * Set the current component state.
	 *
	 * @param Component        $component Component to use.
	 * @param Element_Resolver $parent_resolver Element resolver.
	 * @return void
	 */
	public function on_component( $component, $parent_resolver ) {
		$this->component = $component;

		// Here we will set the component elements on the resolver instance, which will allow
		// the developer to access short-cuts for CSS selectors on the component which can
		// allow for more expressive navigation and interaction with all the components.
		$this->resolver->pageElements(
			$component->elements() + $parent_resolver->elements
		);

		$component->assert( $this );

		$this->resolver->prefix = $this->resolver->format(
			$component->selector()
		);
	}

	/**
	 * Ensure that jQuery is available on the page.
	 *
	 * @return void
	 */
	public function ensurejQueryIsAvailable() {
		if ( $this->driver->executeScript( 'return window.jQuery == null' ) ) {
			$this->driver->executeScript( file_get_contents( __DIR__ . '/../bin/jquery.js' ) );
		}
	}

	/**
	 * Pause for the given amount of milliseconds.
	 *
	 * @param  int $milliseconds Ms to delay.
	 * @return static
	 */
	public function pause( $milliseconds ) {
		usleep( $milliseconds * 1000 );

		return $this;
	}

	/**
	 * Close the browser.
	 *
	 * @return void
	 */
	public function quit() {
		$this->driver->quit();
	}

	/**
	 * Tap the browser into a callback.
	 *
	 * @param  \Closure $callback Callback to tap.
	 * @return static
	 */
	public function tap( $callback ) {
		$callback( $this );

		return $this;
	}

	/**
	 * Dump the content from the last response.
	 *
	 * @return void
	 */
	public function dump() {
		dd( $this->driver->getPageSource() );
	}

	/**
	 * Stop running tests but leave the browser open.
	 *
	 * @return void
	 */
	public function stop() {
		exit();
	}

	/**
	 * Dynamically call a method on the browser.
	 *
	 * @param string $method Method to call.
	 * @param array  $parameters Method parameters.
	 * @return mixed
	 *
	 * @throws BadMethodCallException Thrown on undefined method.
	 */
	public function __call( $method, $parameters ) {
		if ( static::has_macro( $method ) ) {
			return $this->macro_call( $method, $parameters );
		}

		if ( $this->component && method_exists( $this->component, $method ) ) {
			array_unshift( $parameters, $this );

			$this->component->{$method}( ...$parameters );

			return $this;
		}

		if ( $this->page && method_exists( $this->page, $method ) ) {
			array_unshift( $parameters, $this );

			$this->page->{$method}( ...$parameters );

			return $this;
		}

		throw new BadMethodCallException( "Call to undefined method [{$method}]." );
	}
}
