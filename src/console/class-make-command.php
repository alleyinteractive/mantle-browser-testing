<?php
/**
 * Make_Command class file.
 *
 * @package Mantle
 */

namespace Mantle\Browser_Testing\Console;

use Mantle\Framework\Console\Generators\Test_Make_Command;

/**
 * Browser Testing Test Case Generator
 */
class Make_Command extends Test_Make_Command {
	/**
	 * The name of the command.
	 *
	 * @var string
	 */
	protected $name = 'make:browser-testing';

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $signature = 'make:browser-testing {name}';

	/**
	 * Command Description.
	 *
	 * @var string
	 */
	protected $description = 'Generate a browser testing test case.';

	/**
	 * The type of class being generated.
	 *
	 * @var string
	 */
	protected $type = 'Tests\Browser';

	/**
	 * Get the stub file for the generator.
	 *
	 * @return string
	 */
	public function get_file_stub(): string {
		return __DIR__ . '/stubs/test.stub';
	}
}
