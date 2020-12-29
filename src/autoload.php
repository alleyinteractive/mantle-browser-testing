<?php
/**
 * Autoloader file.
 *
 * @package mantle-browser-testing
 */

namespace Mantle\Browser_Testing;

use function Mantle\Framework\generate_wp_autoloader;

spl_autoload_register(
	generate_wp_autoloader( __NAMESPACE__, __DIR__ )
);
