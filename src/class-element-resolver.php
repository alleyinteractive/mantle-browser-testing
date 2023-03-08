<?php
/**
 * Element_Resolver class file.
 *
 * @package mantle-browser-testing
 * phpcs:disable Squiz.Commenting.FunctionComment.EmptyThrows
 * phpcs:disable Squiz.Commenting.EmptyCatchComment.Missing
 * phpcs:disable Generic.CodeAnalysis.EmptyStatement.DetectedCatch
 * phpcs:disable Squiz.PHP.DisallowMultipleAssignments.FoundInControlStructure
 */

namespace Mantle\Browser_Testing;

use Exception;
use Facebook\WebDriver\WebDriverBy;
use InvalidArgumentException;
use Mantle\Support\Str;
use Mantle\Support\Traits\Macroable;

use function Mantle\Support\Helpers\collect;

/**
 * Element Resolver
 */
class Element_Resolver {
	use Macroable;

	/**
	 * The remote web driver instance.
	 *
	 * @var \Facebook\WebDriver\Remote\RemoteWebDriver
	 */
	public $driver;

	/**
	 * The selector prefix for the resolver.
	 *
	 * @var string
	 */
	public $prefix;

	/**
	 * Set the elements the resolver should use as shortcuts.
	 *
	 * @var array
	 */
	public $elements = [];

	/**
	 * The button finding methods.
	 *
	 * @var array
	 */
	protected $button_finders = [
		'findById',
		'findButtonBySelector',
		'findButtonByName',
		'findButtonByValue',
		'findButtonByText',
	];

	/**
	 * Create a new element resolver instance.
	 *
	 * @param  \Facebook\WebDriver\Remote\RemoteWebDriver $driver Driver instance.
	 * @param  string                                     $prefix Prefix to use.
	 * @return void
	 */
	public function __construct( $driver, $prefix = 'body' ) {
		$this->driver = $driver;
		$this->prefix = trim( $prefix );
	}

	/**
	 * Set the page elements the resolver should use as shortcuts.
	 *
	 * @param  array $elements Elements to use.
	 * @return $this
	 */
	public function pageElements( array $elements ) {
		$this->elements = $elements;

		return $this;
	}

	/**
	 * Resolve the element for a given input "field".
	 *
	 * @param  string $field Field name.
	 * @return \Facebook\WebDriver\Remote\RemoteWebElement
	 *
	 * @throws \Exception Thrown on error.
	 */
	public function resolveForTyping( $field ) {
		$element = $this->findById( $field );

		if ( ! is_null( $element ) ) {
			return $element;
		}

		return $this->firstOrFail(
			[
				"input[name='{$field}']",
				"textarea[name='{$field}']",
				$field,
			]
		);
	}

	/**
	 * Resolve the element for a given select "field".
	 *
	 * @param  string $field Field name.
	 * @return \Facebook\WebDriver\Remote\RemoteWebElement
	 *
	 * @throws \Exception Thrown on error resolving.
	 */
	public function resolveForSelection( $field ) {
		$element = $this->findById( $field );

		if ( ! is_null( $element ) ) {
			return $element;
		}

		return $this->firstOrFail(
			[
				"select[name='{$field}']",
				$field,
			]
		);
	}

	/**
	 * Resolve all the options with the given value on the select field.
	 *
	 * @param  string $field Field name.
	 * @param  array  $values Values to resolve.
	 * @return \Facebook\WebDriver\Remote\RemoteWebElement[]
	 *
	 * @throws \Exception Thrown on error resolving.
	 */
	public function resolveSelectOptions( $field, array $values ) {
		$options = $this->resolveForSelection( $field )
				->findElements( WebDriverBy::tagName( 'option' ) );

		if ( empty( $options ) ) {
			return [];
		}

		return array_filter(
			$options,
			function ( $option ) use ( $values ) {
				return in_array( $option->getAttribute( 'value' ), $values );
			}
		);
	}

	/**
	 * Resolve the element for a given radio "field" / value.
	 *
	 * @param  string $field Field name.
	 * @param  string $value Values to resolve.
	 * @return \Facebook\WebDriver\Remote\RemoteWebElement
	 *
	 * @throws \InvalidArgumentException Thrown on invalid arguments.
	 */
	public function resolveForRadioSelection( $field, $value = null ) {
		$element = $this->findById( $field );
		if ( ! is_null( $element ) ) {
			return $element;
		}

		if ( is_null( $value ) ) {
			throw new InvalidArgumentException(
				"No value was provided for radio button [{$field}]."
			);
		}

		return $this->firstOrFail(
			[
				"input[type=radio][name='{$field}'][value='{$value}']",
				$field,
			]
		);
	}

	/**
	 * Resolve the element for a given checkbox "field".
	 *
	 * @param  string|null $field Field name.
	 * @param  string      $value Values to resolve.
	 * @return \Facebook\WebDriver\Remote\RemoteWebElement
	 *
	 * @throws \Exception Thrown on error resolving.
	 */
	public function resolveForChecking( $field, $value = null ) {
		$element = $this->findById( $field );
		if ( ! is_null( $element ) ) {
			return $element;
		}

		$selector = 'input[type=checkbox]';

		if ( ! is_null( $field ) ) {
			$selector .= "[name='{$field}']";
		}

		if ( ! is_null( $value ) ) {
			$selector .= "[value='{$value}']";
		}

		return $this->firstOrFail(
			[
				$selector,
				$field,
			]
		);
	}

	/**
	 * Resolve the element for a given file "field".
	 *
	 * @param  string $field Field name.
	 * @return \Facebook\WebDriver\Remote\RemoteWebElement
	 *
	 * @throws \Exception Thrown on error resolving.
	 */
	public function resolveForAttachment( $field ) {
		$element = $this->findById( $field );
		if ( ! is_null( $element ) ) {
			return $element;
		}

		return $this->firstOrFail(
			[
				"input[type=file][name='{$field}']",
				$field,
			]
		);
	}

	/**
	 * Resolve the element for a given "field".
	 *
	 * @param  string $field Field name.
	 * @return \Facebook\WebDriver\Remote\RemoteWebElement
	 *
	 * @throws \Exception Thrown on error resolving.
	 */
	public function resolveForField( $field ) {
		$element = $this->findById( $field );
		if ( ! is_null( $element ) ) {
			return $element;
		}

		return $this->firstOrFail(
			[
				"input[name='{$field}']",
				"textarea[name='{$field}']",
				"select[name='{$field}']",
				"button[name='{$field}']",
				$field,
			]
		);
	}

	/**
	 * Resolve the element for a given button.
	 *
	 * @param  string $button Button to resolve.
	 * @return \Facebook\WebDriver\Remote\RemoteWebElement
	 *
	 * @throws \InvalidArgumentException Thrown on error resolving.
	 */
	public function resolveForButtonPress( $button ) {
		foreach ( $this->button_finders as $method ) {
			$element = $this->{$method}( $button );
			if ( ! is_null( $element ) ) {
				return $element;
			}
		}

		throw new InvalidArgumentException(
			"Unable to locate button [{$button}]."
		);
	}

	/**
	 * Resolve the element for a given button by selector.
	 *
	 * @param  string $button Button to find.
	 * @return \Facebook\WebDriver\Remote\RemoteWebElement|null
	 */
	protected function findButtonBySelector( $button ) {
		$element = $this->findById( $button );
		if ( ! is_null( $element ) ) {
			return $element;
		}
	}

	/**
	 * Resolve the element for a given button by name.
	 *
	 * @param  string $button Button to find.
	 * @return \Facebook\WebDriver\Remote\RemoteWebElement|null
	 */
	protected function findButtonByName( $button ) {
		if ( ! is_null( $element = $this->find( "input[type=submit][name='{$button}']" ) ) ||
			! is_null( $element = $this->find( "input[type=button][value='{$button}']" ) ) ||
			! is_null( $element = $this->find( "button[name='{$button}']" ) ) ) {
			return $element;
		}
	}

	/**
	 * Resolve the element for a given button by value.
	 *
	 * @param  string $button Button to resolve.
	 * @return \Facebook\WebDriver\Remote\RemoteWebElement|null
	 */
	protected function findButtonByValue( $button ) {
		foreach ( $this->all( 'input[type=submit]' ) as $element ) {
			if ( $element->getAttribute( 'value' ) === $button ) {
				return $element;
			}
		}
	}

	/**
	 * Resolve the element for a given button by text.
	 *
	 * @param  string $button Button to find.
	 * @return \Facebook\WebDriver\Remote\RemoteWebElement|null
	 */
	protected function findButtonByText( $button ) {
		foreach ( $this->all( 'button' ) as $element ) {
			if ( Str::contains( $element->getText(), $button ) ) {
				return $element;
			}
		}
	}

	/**
	 * Attempt to find the selector by ID.
	 *
	 * @param  string $selector CSS selector.
	 * @return \Facebook\WebDriver\Remote\RemoteWebElement|null
	 */
	protected function findById( $selector ) {
		if ( preg_match( '/^#[\w\-:]+$/', $selector ) ) {
			return $this->driver->findElement( WebDriverBy::id( substr( $selector, 1 ) ) );
		}
	}

	/**
	 * Find an element by the given selector or return null.
	 *
	 * @param  string $selector CSS Selector.
	 * @return \Facebook\WebDriver\Remote\RemoteWebElement|null
	 */
	public function find( $selector ) {
		try {
			return $this->findOrFail( $selector );
		} catch ( Exception $e ) {

		}
	}

	/**
	 * Get the first element matching the given selectors.
	 *
	 * @param  array $selectors CSS selectors.
	 * @return \Facebook\WebDriver\Remote\RemoteWebElement
	 *
	 * @throws \Exception Thrown on error resolving.
	 */
	public function firstOrFail( $selectors ) {
		foreach ( (array) $selectors as $selector ) {
			try {
				return $this->findOrFail( $selector );
			} catch ( Exception $e ) {

			}
		}

		throw $e;
	}

	/**
	 * Find an element by the given selector or throw an exception.
	 *
	 * @param  string $selector CSS Selector.
	 * @return \Facebook\WebDriver\Remote\RemoteWebElement
	 */
	public function findOrFail( $selector ) {
		$element = $this->findById( $selector );
		if ( ! is_null( $element ) ) {
			return $element;
		}

		return $this->driver->findElement(
			WebDriverBy::cssSelector( $this->format( $selector ) )
		);
	}

	/**
	 * Find the elements by the given selector or return an empty array.
	 *
	 * @param  string $selector CSS Selector.
	 * @return \Facebook\WebDriver\Remote\RemoteWebElement[]
	 */
	public function all( $selector ) {
		try {
			return $this->driver->findElements(
				WebDriverBy::cssSelector( $this->format( $selector ) )
			);
		} catch ( Exception $e ) {

		}

		return [];
	}

	/**
	 * Format the given selector with the current prefix.
	 *
	 * @param  string $selector CSS Selector.
	 * @return string
	 */
	public function format( $selector ) {
		$sorted_elements = collect( $this->elements )->sort_by_desc(
			function ( $element, $key ) {
				return strlen( $key );
			}
		)->to_array();

		$original_selector = $selector;

		$selector = str_replace(
			array_keys( $sorted_elements ),
			array_values( $sorted_elements ),
			$original_selector
		);

		if ( Str::starts_with( $selector, '@' ) && $selector === $original_selector ) {
			$selector = '[dusk="' . explode( '@', $selector )[1] . '"]';
		}

		return trim( $this->prefix . ' ' . $selector );
	}
}
