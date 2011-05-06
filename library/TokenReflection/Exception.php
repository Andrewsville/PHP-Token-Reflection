<?php
/**
 * PHP Token Reflection
 *
 * Development version
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this library in the file license.txt.
 *
 * @author Ondřej Nešpor <andrew@andrewsville.cz>
 * @author Jaroslav Hanslík <kukulich@kukulich.cz>
 */

namespace TokenReflection;

use Exception as InternalException;

/**
 * Library exception.
 */
class Exception extends InternalException
{
	/**
	 * Exception type - the requested operation is not supported.
	 *
	 * @var integer
	 */
	const UNSUPPORTED = -1;

	/**
	 * Exception type - the processed source is invalid.
	 *
	 * @var integer
	 */
	const INVALID_SOURCE = 0;

	/**
	 * Exception type - the requested file does not exist.
	 *
	 * @var integer
	 */
	const FILE_DOES_NOT_EXIST = 1;

	/**
	 * Exception type - the requested file is not readable.
	 *
	 * @var integer
	 */
	const FILE_NOT_READABLE = 2;

	/**
	 * Exception type - the requested directory does not exist.
	 *
	 * @var integer
	 */
	const DIR_DOES_NOT_EXIST = 3;

	/**
	 * Exception type - no backend was set.
	 *
	 * @var integer
	 */
	const NO_BACKEND_SET = 5;

	/**
	 * Exception type - the reflection class could not be unserialized.
	 *
	 * @var integer
	 */
	const UNSERIALIZATION_ERROR = 10;

	/**
	 * Exception type - the requested reflection object does not exist.
	 *
	 * @var integer
	 */
	const DOES_NOT_EXIST = 15;
}
