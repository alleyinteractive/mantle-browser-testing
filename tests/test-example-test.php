<?php
namespace Create_Mantle_Package\Tests;

use Mantle\Framework\Testing\Framework_Test_Case;

class Test_Example_Test extends Framework_Test_Case {
	public function test_example_test() {
		$this->assertTrue( class_exists( \Create_Mantle_Package\Example_Service_Provider::class ) );
	}
}
