<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\Exception;


/**
 * Runtime exception raised when working with a reflection element.
 */
class RuntimeException extends BaseException
{

	/**
	 * The property/method is not accessible.
	 *
	 * @var int
	 */
	const NOT_ACCESSIBLE = 3002;

}
