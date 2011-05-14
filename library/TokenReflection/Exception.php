<?php
/**
 * PHP Token Reflection
 *
 * Version 1.0beta1
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
abstract class Exception extends InternalException
{
	/**#@+
	 * The requested operation is not supported.
	 *
	 * @var integer
	 */
	const UNSUPPORTED = 1;

	/**
	 * The requested object does not exist.
	 */
	const DOES_NOT_EXIST = 2;
}
