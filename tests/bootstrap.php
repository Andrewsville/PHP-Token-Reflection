<?php

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
