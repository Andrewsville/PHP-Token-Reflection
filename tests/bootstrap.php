<?php
/**
 * PHP Token Reflection
 *
 * Version 1.0 beta 2
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this library in the file LICENSE.
 *
 * @author Ondřej Nešpor <andrew@andrewsville.cz>
 * @author Jaroslav Hanslík <kukulich@kukulich.cz>
 */

// Class search path
set_include_path(
	realpath(__DIR__ . '/..') . PATH_SEPARATOR .   // Libraries
	__DIR__ . PATH_SEPARATOR .   // Library tests
	get_include_path()
);

// Autoload
spl_autoload_register(function($className) {
	$file = str_replace('\\', '/', $className) . '.php';
	$file = str_replace('_', '/', $className) . '.php';
	if (false !== stream_resolve_include_path($file)) {
		require_once $file;
	}
});
