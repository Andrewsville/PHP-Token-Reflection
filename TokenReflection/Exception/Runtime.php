<?php
/**
 * PHP Token Reflection
 *
 * Version 1.0.0 RC 2
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

use TokenReflection;

/**
 * Runtime exception.
 *
 * Thrown while using reflection objects.
 */
class Runtime extends TokenReflection\Exception
{
	/**#@+
	 * Invalid argument was provided.
	 *
	 * @var integer
	 */
	const INVALID_ARGUMENT = 20;

	/**
	 * The requested values/action is not accessible.
	 */
	const NOT_ACCESSBILE = 21;

	/**
	 * The provided reflection already exists.
	 */
	const ALREADY_EXISTS = 22;

	/**
	 * There was a problem with (un)serialization of the Stream.
	 */
	const SERIALIZATION_ERROR = 23;
}
