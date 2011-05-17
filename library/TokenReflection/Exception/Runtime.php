<?php
/**
 * PHP Token Reflection
 *
 * Version 1.0 beta 2
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this library in the file license.txt.
 *
 * @author Ondřej Nešpor <andrew@andrewsville.cz>
 * @author Jaroslav Hanslík <kukulich@kukulich.cz>
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
	 * Token streams are not being stored.
	 *
	 * @var integer
	 */
	const TOKEN_STREAM_STORING_TURNED_OFF = 20;

	/**
	 * Invalid argument was provided.
	 */
	const INVALID_ARGUMENT = 21;

	/**
	 * The requested values/action is not accessible.
	 */
	const NOT_ACCESSBILE = 22;

	/**
	 * The provided reflection already exists.
	 */
	const ALREADY_EXISTS = 23;
}
