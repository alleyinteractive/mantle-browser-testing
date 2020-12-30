<?php
/**
 * Interacts_With_Elements trait file.
 *
 * @package mantle-browser-testing
 */

namespace Mantle\Browser_Testing\Concerns;

use Facebook\WebDriver\Interactions\WebDriverActions;
use Facebook\WebDriver\Remote\LocalFileDetector;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverKeys;
use Mantle\Framework\Support\Str;

/**
 * Concern for interacitons with elements.
 */
trait Interacts_With_Elements {

	/**
	 * Get all of the elements matching the given selector.
	 *
	 * @param  string $selector CSS selector.
	 * @return \Facebook\WebDriver\Remote\RemoteWebElement[]
	 */
	public function elements( $selector ) {
		return $this->resolver->all( $selector );
	}

	/**
	 * Get the element matching the given selector.
	 *
	 * @param  string $selector CSS selector.
	 * @return \Facebook\WebDriver\Remote\RemoteWebElement|null
	 */
	public function element( $selector ) {
		return $this->resolver->find( $selector );
	}

	/**
	 * Click the link with the given text.
	 *
	 * @param  string $link Link to click.
	 * @param  string $element Element name.
	 * @return static
	 */
	public function clickLink( $link, $element = 'a' ) {
		$this->ensurejQueryIsAvailable();

		$selector = addslashes( trim( $this->resolver->format( "{$element}:contains({$link}):visible" ) ) );

		$this->driver->executeScript( "jQuery.find(\"{$selector}\")[0].click();" );

		return $this;
	}

	/**
	 * Directly get or set the value attribute of an input field.
	 *
	 * @param  string      $selector CSS selector.
	 * @param  string|null $value Value to use.
	 * @return static
	 */
	public function value( $selector, $value = null ) {
		if ( is_null( $value ) ) {
			return $this->resolver->findOrFail( $selector )->getAttribute( 'value' );
		}

		$selector = $this->resolver->format( $selector );

		$this->driver->executeScript(
			'document.querySelector(' . wp_json_encode( $selector ) . ').value = ' . wp_json_encode( $value ) . ';'
		);

		return $this;
	}

	/**
	 * Get the text of the element matching the given selector.
	 *
	 * @param  string $selector CSS selector.
	 * @return string
	 */
	public function text( $selector ) {
		return $this->resolver->findOrFail( $selector )->getText();
	}

	/**
	 * Get the given attribute from the element matching the given selector.
	 *
	 * @param  string $selector CSS selector.
	 * @param  string $attribute Attribute to use.
	 * @return string
	 */
	public function attribute( $selector, $attribute ) {
		return $this->resolver->findOrFail( $selector )->getAttribute( $attribute );
	}

	/**
	 * Send the given keys to the element matching the given selector.
	 *
	 * @param  string $selector CSS selector.
	 * @param  mixed  ...$keys Keys to use.
	 * @return static
	 */
	public function keys( $selector, ...$keys ) {
		$this->resolver->findOrFail( $selector )->sendKeys( $this->parseKeys( $keys ) );

		return $this;
	}

	/**
	 * Parse the keys before sending to the keyboard.
	 *
	 * @param  array $keys Keys to use.
	 * @return array
	 */
	protected function parseKeys( $keys ): array {
		return collect( $keys )->map(
			function ( $key ) {
				if ( is_string( $key ) && Str::starts_with( $key, '{' ) && Str::ends_with( $key, '}' ) ) {
					$key = constant( WebDriverKeys::class . '::' . strtoupper( trim( $key, '{}' ) ) );
				}

				if ( is_array( $key ) && Str::starts_with( $key[0], '{' ) ) {
					$key[0] = constant( WebDriverKeys::class . '::' . strtoupper( trim( $key[0], '{}' ) ) );
				}

				return $key;
			}
		)->all();
	}

	/**
	 * Type the given value in the given field.
	 *
	 * @param  string $field Field name.
	 * @param  string $value Value to use.
	 * @return static
	 */
	public function type( $field, $value ) {
		$this->resolver->resolveForTyping( $field )->clear()->sendKeys( $value );

		return $this;
	}

	/**
	 * Type the given value in the given field slowly.
	 *
	 * @param  string $field Field name.
	 * @param  string $value Value to use.
	 * @param  int    $pause Pause to use in ms.
	 * @return static
	 */
	public function typeSlowly( $field, $value, $pause = 100 ) {
		$this->clear( $field )->appendSlowly( $field, $value, $pause );

		return $this;
	}

	/**
	 * Type the given value in the given field without clearing it.
	 *
	 * @param  string $field Field name.
	 * @param  string $value Value to use.
	 * @return static
	 */
	public function append( $field, $value ) {
		$this->resolver->resolveForTyping( $field )->sendKeys( $value );

		return $this;
	}

	/**
	 * Type the given value in the given field slowly without clearing it.
	 *
	 * @param  string $field Field name.
	 * @param  string $value Value to use.
	 * @param  int    $pause Pause to use in ms.
	 * @return static
	 */
	public function appendSlowly( $field, $value, $pause = 100 ) {
		foreach ( str_split( $value ) as $char ) {
			$this->append( $field, $char )->pause( $pause );
		}

		return $this;
	}

	/**
	 * Clear the given field.
	 *
	 * @param  string $field Field name.
	 * @return static
	 */
	public function clear( $field ) {
		$this->resolver->resolveForTyping( $field )->clear();

		return $this;
	}

	/**
	 * Select the given value or random value of a drop-down field.
	 *
	 * @param  string $field Field name.
	 * @param  string $value Value to use.
	 * @return static
	 */
	public function select( $field, $value = null ) {
		$element = $this->resolver->resolveForSelection( $field );

		$options = $element->findElements( WebDriverBy::cssSelector( 'option:not([disabled])' ) );

		if ( func_num_args() === 1 ) {
			$options[ array_rand( $options ) ]->click();
		} else {
			if ( is_bool( $value ) ) {
				$value = $value ? '1' : '0';
			}

			foreach ( $options as $option ) {
				if ( (string) $option->getAttribute( 'value' ) === (string) $value ) {
					$option->click();

					break;
				}
			}
		}

		return $this;
	}

	/**
	 * Select the given value of a radio button field.
	 *
	 * @param  string $field Field name.
	 * @param  string $value Value to use.
	 * @return static
	 */
	public function radio( $field, $value ) {
		$this->resolver->resolveForRadioSelection( $field, $value )->click();

		return $this;
	}

	/**
	 * Check the given checkbox.
	 *
	 * @param  string $field Field name.
	 * @param  string $value Value to use.
	 * @return static
	 */
	public function check( $field, $value = null ) {
		$element = $this->resolver->resolveForChecking( $field, $value );

		if ( ! $element->isSelected() ) {
			$element->click();
		}

		return $this;
	}

	/**
	 * Uncheck the given checkbox.
	 *
	 * @param  string $field Field name.
	 * @param  string $value Value to use.
	 * @return static
	 */
	public function uncheck( $field, $value = null ) {
		$element = $this->resolver->resolveForChecking( $field, $value );

		if ( $element->isSelected() ) {
			$element->click();
		}

		return $this;
	}

	/**
	 * Attach the given file to the field.
	 *
	 * @param  string $field Field name.
	 * @param  string $path Path to use.
	 * @return static
	 */
	public function attach( $field, $path ) {
		$element = $this->resolver->resolveForAttachment( $field );

		$element->setFileDetector( new LocalFileDetector() )->sendKeys( $path );

		return $this;
	}

	/**
	 * Press the button with the given text or name.
	 *
	 * @param  string $button Button to press.
	 * @return static
	 */
	public function press( $button ) {
		$this->resolver->resolveForButtonPress( $button )->click();

		return $this;
	}

	/**
	 * Press the button with the given text or name.
	 *
	 * @param  string $button Button to press.
	 * @param  int    $seconds Delay in ms.
	 * @return static
	 */
	public function pressAndWaitFor( $button, $seconds = 5 ) {
		$element = $this->resolver->resolveForButtonPress( $button );

		$element->click();

		return $this->waitUsing(
			$seconds,
			100,
			function () use ( $element ) {
				return $element->isEnabled();
			}
		);
	}

	/**
	 * Drag an element to another element using selectors.
	 *
	 * @param  string $from Drag from.
	 * @param  string $to Drag to.
	 * @return static
	 */
	public function drag( $from, $to ) {
		( new WebDriverActions( $this->driver ) )->dragAndDrop(
			$this->resolver->findOrFail( $from ),
			$this->resolver->findOrFail( $to )
		)->perform();

		return $this;
	}

	/**
	 * Drag an element up.
	 *
	 * @param  string $selector CSS selector.
	 * @param  int    $offset Offset to use.
	 * @return static
	 */
	public function dragUp( $selector, $offset ) {
		return $this->dragOffset( $selector, 0, -$offset );
	}

	/**
	 * Drag an element down.
	 *
	 * @param  string $selector CSS selector.
	 * @param  int    $offset Offset to use.
	 * @return static
	 */
	public function dragDown( $selector, $offset ) {
		return $this->dragOffset( $selector, 0, $offset );
	}

	/**
	 * Drag an element to the left.
	 *
	 * @param  string $selector CSS selector.
	 * @param  int    $offset Offset to use.
	 * @return static
	 */
	public function dragLeft( $selector, $offset ) {
		return $this->dragOffset( $selector, -$offset, 0 );
	}

	/**
	 * Drag an element to the right.
	 *
	 * @param  string $selector CSS selector.
	 * @param  int    $offset Offset to use.
	 * @return static
	 */
	public function dragRight( $selector, $offset ) {
		return $this->dragOffset( $selector, $offset, 0 );
	}

	/**
	 * Drag an element by the given offset.
	 *
	 * @param  string $selector CSS selector.
	 * @param  int    $x X offset.
	 * @param  int    $y Y offset.
	 * @return static
	 */
	public function dragOffset( $selector, $x = 0, $y = 0 ) {
		( new WebDriverActions( $this->driver ) )->dragAndDropBy(
			$this->resolver->findOrFail( $selector ),
			$x,
			$y
		)->perform();

		return $this;
	}

	/**
	 * Accept a JavaScript dialog.
	 *
	 * @return static
	 */
	public function acceptDialog() {
		$this->driver->switchTo()->alert()->accept();

		return $this;
	}

	/**
	 * Type the given value in an open JavaScript prompt dialog.
	 *
	 * @param  string $value Value to use.
	 * @return static
	 */
	public function typeInDialog( $value ) {
		$this->driver->switchTo()->alert()->sendKeys( $value );

		return $this;
	}

	/**
	 * Dismiss a JavaScript dialog.
	 *
	 * @return static
	 */
	public function dismissDialog() {
		$this->driver->switchTo()->alert()->dismiss();

		return $this;
	}
}
