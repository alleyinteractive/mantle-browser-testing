<?php
/**
 * Create Mantle Package Autoloader
 *
 * @package create-mantle-package
 */

namespace Create_Mantle_Package;

use function Mantle\Framework\generate_wp_autoloader;

spl_autoload_register(
	generate_wp_autoloader( __NAMESPACE__, __DIR__ )
);
