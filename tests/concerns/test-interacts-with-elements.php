<?php

namespace Mantle\Browser_Testing\Tests\Concerns;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Mantle\Browser_Testing\Concerns\Interacts_With_Elements as Interacts_With_Elements;
use Mantle\Browser_Testing\Element_Resolver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Mantle\\Browser_Testing\Concerns\Interacts_With_Elements
 */
class Test_Interacts_With_Elements extends TestCase {

	/** @var Interacts_With_Elements */
	protected $trait;

	/** @var ElementResolver|MockObject */
	protected $resolver;

	/** @var RemoteWebDriver|MockObject */
	protected $driver;

	public function dataProviderValueWithValue() {
		return [
			[ '#nuff', 'narf', 'document.querySelector("#nuff").value = "narf";' ],
			[ '#nu\'ff', 'n\'arf', 'document.querySelector("#nu\'ff").value = "n\'arf";' ],
			[ '#nu"ff', 'n"arf', 'document.querySelector("#nu\\"ff").value = "n\\"arf";' ],
			[ "#\nuff", "\narf", 'document.querySelector("#\\nuff").value = "\\narf";' ],
			[ "#\nuff\xc3\xa9", "\narf\xc3\xa9", 'document.querySelector("#\\nuff\u00e9").value = "\\narf\u00e9";' ],
			[ 42, false, 'document.querySelector(42).value = false;' ],
			[ 'a', [ 'x' => 'y' ], 'document.querySelector("a").value = {"x":"y"};' ],
			[ 'a', [ 'y' ], 'document.querySelector("a").value = ["y"];' ],
		];
	}

	protected function setUp(): void {
		parent::setUp();

		$this->resolver = $this->getMockBuilder( Element_Resolver::class )->setMethods( [ 'findOrFail', 'format' ] )->disableOriginalConstructor()->getMock();
		$this->driver   = $this->getMockBuilder( RemoteWebDriver::class )->setMethods( [ 'executeScript' ] )->disableOriginalConstructor()->getMock();

		$this->trait = new class($this->resolver, $this->driver) {
			use Interacts_With_Elements;

			/** @var ElementResolver|MockObject */
			public $resolver;

			/** @var RemoteWebDriver|MockObject */
			public $driver;

			public function __construct( Element_Resolver $resolver, RemoteWebDriver $driver ) {
				$this->resolver = $resolver;
				$this->driver   = $driver;
			}
		};
	}

	protected function tearDown(): void {
		$this->trait->resolver = null;
		$this->trait->driver   = null;
		$this->trait           = null;

		$this->resolver = null;
		$this->driver   = null;

		parent::tearDown();
	}

	/**
	 * @covers ::value
	 * @dataProvider dataProviderValueWithValue
	 * @param mixed  $selector
	 * @param mixed  $value
	 * @param string $js
	 */
	public function testValueWithValue( $selector, $value, string $js ) {
		$this->resolver->expects( static::never() )->method( 'findOrFail' );
		$this->resolver->expects( static::once() )->method( 'format' )->with( $selector )->willReturn( $selector );

		$this->driver->expects( static::once() )->method( 'executeScript' )->with( $js )->willReturn( 42 );

		static::assertSame( $this->trait, $this->trait->value( $selector, $value ) );
	}

	/**
	 * @covers ::value
	 */
	public function testValueWithoutValue() {
		$selector = '#nuff';

		/** @var RemoteWebElement|MockObject $resolver */
		$remoteElement = $this->getMockBuilder( RemoteWebElement::class )->setMethods( [ 'getAttribute' ] )->disableOriginalConstructor()->getMock();
		$remoteElement->expects( static::once() )->method( 'getAttribute' )->with( 'value' )->willReturn( 'null' );

		$this->resolver->expects( static::once() )->method( 'findOrFail' )->with( $selector )->willReturn( $remoteElement );
		$this->resolver->expects( static::never() )->method( 'format' );

		$this->driver = $this->getMockBuilder( RemoteWebDriver::class )->setMethods( [ 'executeScript' ] )->disableOriginalConstructor()->getMock();
		$this->driver->expects( static::never() )->method( 'executeScript' );

		static::assertSame( 'null', $this->trait->value( $selector ) );
	}
}
