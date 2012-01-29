<?php
/**
 * PHP Token Reflection
 *
 * Version 1.0.2
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this library in the file LICENSE.
 *
 * @author Ondřej Nešpor
 * @author Jaroslav Hanslík
 */

namespace TokenReflection\Exception;

use RuntimeException;

/**
 * Base TokenReflection exception.
 */
abstract class BaseException extends RuntimeException
{
	/**
	 * The property/element does not exist.
	 *
	 * @var integer
	 */
	const DOES_NOT_EXIST = 1;

	/**
	 * An invalid argument was provided.
	 *
	 * @var integer
	 */
	const INVALID_ARGUMENT = 2;

	/**
	 * A required PHP extension is missing.
	 *
	 * @var integer
	 */
	const PHP_EXT_MISSING = 3;

	/**
	 * The requested feature is not supported.
	 *
	 * @var integer
	 */
	const UNSUPPORTED = 4;

	/**
	 * Returns a textual description of the exception.
	 *
	 * @return string
	 */
	//abstract function __toString();
}
