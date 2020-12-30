<?php
/**
 * Makes_Assertions trait file.
 *
 * @package mantle-browser-testing
 */

namespace Mantle\Browser_Testing\Concerns;

use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Illuminate\Support\Str;
use PHPUnit\Framework\Assert as PHPUnit;

/**
 * Concern for interactions
 */
trait Makes_Assertions {

	/**
	 * Indicates the browser has made an assertion about the source code of the page.
	 *
	 * @var bool
	 */
	public $made_source_assertion = false;

	/**
	 * Assert that the page title matches the given text.
	 *
	 * @param  string $title Title to assert.
	 * @return static
	 */
	public function assertTitle( $title ) {
		PHPUnit::assertEquals(
			$title,
			$this->driver->getTitle(),
			"Expected title [{$title}] does not equal actual title [{$this->driver->getTitle()}]."
		);

		return $this;
	}

	/**
	 * Assert that the page title contains the given text.
	 *
	 * @param  string $title Title to assert.
	 * @return static
	 */
	public function assertTitleContains( $title ) {
		PHPUnit::assertTrue(
			Str::contains( $this->driver->getTitle(), $title ),
			"Did not see expected text [{$title}] within title [{$this->driver->getTitle()}]."
		);

		return $this;
	}

	/**
	 * Assert that the given encrypted cookie is present.
	 *
	 * @param  string $name Name to assert.
	 * @return static
	 */
	public function assertHasCookie( $name ) {
		$cookie = $this->plainCookie( $name );

		PHPUnit::assertTrue(
			! is_null( $cookie ),
			"Did not find expected cookie [{$name}]."
		);

		return $this;
	}

	/**
	 * Assert that the given encrypted cookie is not present.
	 *
	 * @param  string $name Cookie name.
	 * @return static
	 */
	public function assertCookieMissing( $name ) {
		$cookie = $this->plainCookie( $name );

		PHPUnit::assertTrue(
			is_null( $cookie ),
			"Found unexpected cookie [{$name}]."
		);

		return $this;
	}

	/**
	 * Assert that an encrypted cookie has a given value.
	 *
	 * @param  string $name Cookie name.
	 * @param  string $value Cookie value.
	 * @return static
	 */
	public function assertCookieValue( $name, $value ) {
		$actual = $this->plainCookie( $name );

		PHPUnit::assertEquals(
			$value,
			$actual,
			"Cookie [{$name}] had value [{$actual}], but expected [{$value}]."
		);

		return $this;
	}

	/**
	 * Assert that the given text is present on the page.
	 *
	 * @param  string $text Text to see.
	 * @return static
	 */
	public function assertSee( $text ) {
		return $this->assertSeeIn( '', $text );
	}

	/**
	 * Assert that the given text is not present on the page.
	 *
	 * @param  string $text Text to compare.
	 * @return static
	 */
	public function assertDontSee( $text ) {
		return $this->assertDontSeeIn( '', $text );
	}

	/**
	 * Assert that the given text is present within the selector.
	 *
	 * @param  string $selector CSS selector.
	 * @param  string $text Text to compare.
	 * @return static
	 */
	public function assertSeeIn( $selector, $text ) {
		$full_selector = $this->resolver->format( $selector );

		$element = $this->resolver->findOrFail( $selector );

		PHPUnit::assertTrue(
			Str::contains( $element->getText(), $text ),
			"Did not see expected text [{$text}] within element [{$full_selector}]."
		);

		return $this;
	}

	/**
	 * Assert that the given text is not present within the selector.
	 *
	 * @param  string $selector CSS selector.
	 * @param  string $text Text to compare.
	 * @return static
	 */
	public function assertDontSeeIn( $selector, $text ) {
		$full_selector = $this->resolver->format( $selector );

		$element = $this->resolver->findOrFail( $selector );

		PHPUnit::assertFalse(
			Str::contains( $element->getText(), $text ),
			"Saw unexpected text [{$text}] within element [{$full_selector}]."
		);

		return $this;
	}

	/**
	 * Assert that any text is present within the selector.
	 *
	 * @param  string $selector CSS selector.
	 * @return static
	 */
	public function assertSeeAnythingIn( $selector ) {
		$full_selector = $this->resolver->format( $selector );

		$element = $this->resolver->findOrFail( $selector );

		PHPUnit::assertTrue(
			$element->getText() !== '',
			"Saw unexpected text [''] within element [{$full_selector}]."
		);

		return $this;
	}

	/**
	 * Assert that no text is present within the selector.
	 *
	 * @param  string $selector CSS selector.
	 * @return static
	 */
	public function assertSeeNothingIn( $selector ) {
		$full_selector = $this->resolver->format( $selector );

		$element = $this->resolver->findOrFail( $selector );

		PHPUnit::assertTrue(
			$element->getText() === '',
			"Did not see expected text [''] within element [{$full_selector}]."
		);

		return $this;
	}

	/**
	 * Assert that the given JavaScript expression evaluates to the given value.
	 *
	 * @param  string $expression Expression to use.
	 * @param  mixed  $expected Expected value.
	 * @return static
	 */
	public function assertScript( $expression, $expected = true ) {
		$expression = Str::start( $expression, 'return ' );

		PHPUnit::assertEquals(
			$expected,
			$this->driver->executeScript( $expression ),
			"JavaScript expression [{$expression}] mismatched."
		);

		return $this;
	}

	/**
	 * Assert that the given source code is present on the page.
	 *
	 * @param  string $code Code to check.
	 * @return static
	 */
	public function assertSourceHas( $code ) {
		$this->made_source_assertion = true;

		PHPUnit::assertTrue(
			Str::contains( $this->driver->getPageSource(), $code ),
			"Did not find expected source code [{$code}]."
		);

		return $this;
	}

	/**
	 * Assert that the given source code is not present on the page.
	 *
	 * @param  string $code Code to check.
	 * @return static
	 */
	public function assertSourceMissing( $code ) {
		$this->made_source_assertion = true;

		PHPUnit::assertFalse(
			Str::contains( $this->driver->getPageSource(), $code ),
			"Found unexpected source code [{$code}]."
		);

		return $this;
	}

	/**
	 * Assert that the given link is present on the page.
	 *
	 * @param  string $link Link to check.
	 * @return static
	 */
	public function assertSeeLink( $link ) {
		if ( $this->resolver->prefix ) {
			$message = "Did not see expected link [{$link}] within [{$this->resolver->prefix}].";
		} else {
			$message = "Did not see expected link [{$link}].";
		}

		PHPUnit::assertTrue(
			$this->seeLink( $link ),
			$message
		);

		return $this;
	}

	/**
	 * Assert that the given link is not present on the page.
	 *
	 * @param  string $link Link to check.
	 * @return static
	 */
	public function assertDontSeeLink( $link ) {
		if ( $this->resolver->prefix ) {
			$message = "Saw unexpected link [{$link}] within [{$this->resolver->prefix}].";
		} else {
			$message = "Saw unexpected link [{$link}].";
		}

		PHPUnit::assertFalse(
			$this->seeLink( $link ),
			$message
		);

		return $this;
	}

	/**
	 * Determine if the given link is visible.
	 *
	 * @param  string $link Link to compare.
	 * @return bool
	 */
	public function seeLink( $link ) {
		$this->ensurejQueryIsAvailable();

		$selector = addslashes( trim( $this->resolver->format( "a:contains('{$link}')" ) ) );

		$script = <<<JS
            var link = jQuery.find("{$selector}");
            return link.length > 0 && jQuery(link).is(':visible');
JS;

		return $this->driver->executeScript( $script );
	}

	/**
	 * Assert that the given input field has the given value.
	 *
	 * @param  string $field Field name.
	 * @param  string $value Field value.
	 * @return static
	 */
	public function assertInputValue( $field, $value ) {
		PHPUnit::assertEquals(
			$value,
			$this->inputValue( $field ),
			"Expected value [{$value}] for the [{$field}] input does not equal the actual value [{$this->inputValue($field)}]."
		);

		return $this;
	}

	/**
	 * Assert that the given input field does not have the given value.
	 *
	 * @param  string $field Field name.
	 * @param  string $value Field value.
	 * @return static
	 */
	public function assertInputValueIsNot( $field, $value ) {
		PHPUnit::assertNotEquals(
			$value,
			$this->inputValue( $field ),
			"Value [{$value}] for the [{$field}] input should not equal the actual value."
		);

		return $this;
	}

	/**
	 * Get the value of the given input or text area field.
	 *
	 * @param  string $field Field name.
	 * @return string
	 */
	public function inputValue( $field ) {
		$element = $this->resolver->resolveForTyping( $field );

		return in_array( $element->getTagName(), [ 'input', 'textarea' ] )
						? $element->getAttribute( 'value' )
						: $element->getText();
	}

	/**
	 * Assert that the given checkbox is checked.
	 *
	 * @param  string $field Field name.
	 * @param  string $value Field value.
	 * @return static
	 */
	public function assertChecked( $field, $value = null ) {
		$element = $this->resolver->resolveForChecking( $field, $value );

		PHPUnit::assertTrue(
			$element->isSelected(),
			"Expected checkbox [{$field}] to be checked, but it wasn't."
		);

		return $this;
	}

	/**
	 * Assert that the given checkbox is not checked.
	 *
	 * @param  string $field Field name.
	 * @param  string $value Field value.
	 * @return static
	 */
	public function assertNotChecked( $field, $value = null ) {
		$element = $this->resolver->resolveForChecking( $field, $value );

		PHPUnit::assertFalse(
			$element->isSelected(),
			"Checkbox [{$field}] was unexpectedly checked."
		);

		return $this;
	}

	/**
	 * Assert that the given radio field is selected.
	 *
	 * @param  string $field Field name.
	 * @param  string $value Field value.
	 * @return static
	 */
	public function assertRadioSelected( $field, $value ) {
		$element = $this->resolver->resolveForRadioSelection( $field, $value );

		PHPUnit::assertTrue(
			$element->isSelected(),
			"Expected radio [{$field}] to be selected, but it wasn't."
		);

		return $this;
	}

	/**
	 * Assert that the given radio field is not selected.
	 *
	 * @param  string $field Field name.
	 * @param  string $value Field value.
	 * @return static
	 */
	public function assertRadioNotSelected( $field, $value = null ) {
		$element = $this->resolver->resolveForRadioSelection( $field, $value );

		PHPUnit::assertFalse(
			$element->isSelected(),
			"Radio [{$field}] was unexpectedly selected."
		);

		return $this;
	}

	/**
	 * Assert that the given dropdown has the given value selected.
	 *
	 * @param  string $field Field name.
	 * @param  string $value Field value.
	 * @return static
	 */
	public function assertSelected( $field, $value ) {
		PHPUnit::assertTrue(
			$this->selected( $field, $value ),
			"Expected value [{$value}] to be selected for [{$field}], but it wasn't."
		);

		return $this;
	}

	/**
	 * Assert that the given dropdown does not have the given value selected.
	 *
	 * @param  string $field Field name.
	 * @param  string $value Field value.
	 * @return static
	 */
	public function assertNotSelected( $field, $value ) {
		PHPUnit::assertFalse(
			$this->selected( $field, $value ),
			"Unexpected value [{$value}] selected for [{$field}]."
		);

		return $this;
	}

	/**
	 * Assert that the given array of values are available to be selected.
	 *
	 * @param  string $field Field name.
	 * @param  array  $values Field value.
	 * @return static
	 */
	public function assertSelectHasOptions( $field, array $values ) {
		$options = $this->resolver->resolveSelectOptions( $field, $values );

		$options = collect( $options )->unique(
			function ( RemoteWebElement $option ) {
				return $option->getAttribute( 'value' );
			}
		)->all();

		PHPUnit::assertCount(
			count( $values ),
			$options,
			'Expected options [' . implode( ',', $values ) . "] for selection field [{$field}] to be available."
		);

		return $this;
	}

	/**
	 * Assert that the given array of values are not available to be selected.
	 *
	 * @param  string $field Field name.
	 * @param  array  $values Field values.
	 * @return static
	 */
	public function assertSelectMissingOptions( $field, array $values ) {
		PHPUnit::assertCount(
			0,
			$this->resolver->resolveSelectOptions( $field, $values ),
			'Unexpected options [' . implode( ',', $values ) . "] for selection field [{$field}]."
		);

		return $this;
	}

	/**
	 * Assert that the given value is available to be selected on the given field.
	 *
	 * @param  string $field Field name.
	 * @param  string $value Field value.
	 * @return static
	 */
	public function assertSelectHasOption( $field, $value ) {
		return $this->assertSelectHasOptions( $field, [ $value ] );
	}

	/**
	 * Assert that the given value is not available to be selected.
	 *
	 * @param  string $field Field name.
	 * @param  string $value Field value.
	 * @return static
	 */
	public function assertSelectMissingOption( $field, $value ) {
		return $this->assertSelectMissingOptions( $field, [ $value ] );
	}

	/**
	 * Determine if the given value is selected for the given select field.
	 *
	 * @param  string $field Field name.
	 * @param  string $value Field value.
	 * @return bool
	 */
	public function selected( $field, $value ) {
		$options = $this->resolver->resolveSelectOptions( $field, (array) $value );

		return collect( $options )->contains(
			function ( RemoteWebElement $option ) {
				return $option->isSelected();
			}
		);
	}

	/**
	 * Assert that the element matching the given selector has the given value.
	 *
	 * @param  string $selector CSS selector.
	 * @param  string $value Value to compare.
	 * @return static
	 */
	public function assertValue( $selector, $value ) {
		$full_selector = $this->resolver->format( $selector );

		$actual = $this->resolver->findOrFail( $selector )->getAttribute( 'value' );

		PHPUnit::assertEquals(
			$value,
			$actual,
			"Did not see expected value [{$value}] within element [{$full_selector}]."
		);

		return $this;
	}

	/**
	 * Assert that the element matching the given selector has the given value in the provided attribute.
	 *
	 * @param  string $selector CSS selector.
	 * @param  string $attribute Attribute name.
	 * @param  string $value Value to compare.
	 * @return static
	 */
	public function assertAttribute( $selector, $attribute, $value ) {
		$full_selector = $this->resolver->format( $selector );

		$actual = $this->resolver->findOrFail( $selector )->getAttribute( $attribute );

		PHPUnit::assertNotNull(
			$actual,
			"Did not see expected attribute [{$attribute}] within element [{$full_selector}]."
		);

		PHPUnit::assertEquals(
			$value,
			$actual,
			"Expected '$attribute' attribute [{$value}] does not equal actual value [$actual]."
		);

		return $this;
	}

	/**
	 * Assert that the element matching the given selector has the given value in the provided aria attribute.
	 *
	 * @param  string $selector CSS selector.
	 * @param  string $attribute Attribute to check.
	 * @param  string $value Value to compare.
	 * @return static
	 */
	public function assertAriaAttribute( $selector, $attribute, $value ) {
		return $this->assertAttribute( $selector, 'aria-' . $attribute, $value );
	}

	/**
	 * Assert that the element matching the given selector has the given value in the provided data attribute.
	 *
	 * @param  string $selector CSS selector.
	 * @param  string $attribute Attribute to check.
	 * @param  string $value Value to compare.
	 * @return static
	 */
	public function assertDataAttribute( $selector, $attribute, $value ) {
		return $this->assertAttribute( $selector, 'data-' . $attribute, $value );
	}

	/**
	 * Assert that the element matching the given selector is visible.
	 *
	 * @param  string $selector CSS selector.
	 * @return static
	 */
	public function assertVisible( $selector ) {
		$full_selector = $this->resolver->format( $selector );

		PHPUnit::assertTrue(
			$this->resolver->findOrFail( $selector )->isDisplayed(),
			"Element [{$full_selector}] is not visible."
		);

		return $this;
	}

	/**
	 * Assert that the element matching the given selector is present.
	 *
	 * @param  string $selector CSS selector.
	 * @return static
	 */
	public function assertPresent( $selector ) {
		$full_selector = $this->resolver->format( $selector );

		PHPUnit::assertTrue(
			! is_null( $this->resolver->find( $selector ) ),
			"Element [{$full_selector}] is not present."
		);

		return $this;
	}

	/**
	 * Assert that the element matching the given selector is not visible.
	 *
	 * @param  string $selector CSS selector.
	 * @return static
	 */
	public function assertMissing( $selector ) {
		$full_selector = $this->resolver->format( $selector );

		try {
			$missing = ! $this->resolver->findOrFail( $selector )->isDisplayed();
		} catch ( NoSuchElementException $e ) {
			$missing = true;
		}

		PHPUnit::assertTrue(
			$missing,
			"Saw unexpected element [{$full_selector}]."
		);

		return $this;
	}

	/**
	 * Assert that a JavaScript dialog with the given message has been opened.
	 *
	 * @param  string $message Message to compare.
	 * @return static
	 */
	public function assertDialogOpened( $message ) {
		$actual = $this->driver->switchTo()->alert()->getText();

		PHPUnit::assertEquals(
			$message,
			$actual,
			"Expected dialog message [{$message}] does not equal actual message [{$actual}]."
		);

		return $this;
	}

	/**
	 * Assert that the given field is enabled.
	 *
	 * @param  string $field Field to check.
	 * @return static
	 */
	public function assertEnabled( $field ) {
		$element = $this->resolver->resolveForField( $field );

		PHPUnit::assertTrue(
			$element->isEnabled(),
			"Expected element [{$field}] to be enabled, but it wasn't."
		);

		return $this;
	}

	/**
	 * Assert that the given field is disabled.
	 *
	 * @param  string $field Field to compare.
	 * @return static
	 */
	public function assertDisabled( $field ) {
		$element = $this->resolver->resolveForField( $field );

		PHPUnit::assertFalse(
			$element->isEnabled(),
			"Expected element [{$field}] to be disabled, but it wasn't."
		);

		return $this;
	}

	/**
	 * Assert that the given button is enabled.
	 *
	 * @param  string $button Button to check.
	 * @return static
	 */
	public function assertButtonEnabled( $button ) {
		$element = $this->resolver->resolveForButtonPress( $button );

		PHPUnit::assertTrue(
			$element->isEnabled(),
			"Expected button [{$button}] to be enabled, but it wasn't."
		);

		return $this;
	}

	/**
	 * Assert that the given button is disabled.
	 *
	 * @param  string $button Button to check.
	 * @return static
	 */
	public function assertButtonDisabled( $button ) {
		$element = $this->resolver->resolveForButtonPress( $button );

		PHPUnit::assertFalse(
			$element->isEnabled(),
			"Expected button [{$button}] to be disabled, but it wasn't."
		);

		return $this;
	}

	/**
	 * Assert that the given field is focused.
	 *
	 * @param  string $field Field to check.
	 * @return static
	 */
	public function assertFocused( $field ) {
		$element = $this->resolver->resolveForField( $field );

		PHPUnit::assertTrue(
			$this->driver->switchTo()->activeElement()->equals( $element ),
			"Expected element [{$field}] to be focused, but it wasn't."
		);

		return $this;
	}

	/**
	 * Assert that the given field is not focused.
	 *
	 * @param  string $field Field to check.
	 * @return static
	 */
	public function assertNotFocused( $field ) {
		$element = $this->resolver->resolveForField( $field );

		PHPUnit::assertFalse(
			$this->driver->switchTo()->activeElement()->equals( $element ),
			"Expected element [{$field}] not to be focused, but it was."
		);

		return $this;
	}
}
