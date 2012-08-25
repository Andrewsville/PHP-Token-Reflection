<?php
/**
 * PHP Token Reflection
 *
 * Version 1.3.1
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this library in the file LICENSE.
 *
 * @author Ondřej Nešpor
 * @author Jaroslav Hanslík
 */

// Class search path
set_include_path(
	realpath(__DIR__ . '/..') . PATH_SEPARATOR .   // Library
	__DIR__ . PATH_SEPARATOR .   // Library tests
	get_include_path()
);

// Autoload
spl_autoload_register(function($className) {
	$file = strtr($className, '\\_', DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR) . '.php';
	if (!function_exists('stream_resolve_include_path') || false !== stream_resolve_include_path($file)) {
		@include_once $file;
	}
});
