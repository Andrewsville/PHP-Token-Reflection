<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\Exception;


class ParseException extends StreamException
{

	/**
	 * An unexpected  token was encountered.
	 *
	 * @var int
	 */
	const UNEXPECTED_TOKEN = 1101;

	/**
	 * A logical error was encountered.
	 *
	 * @var int
	 */
	const LOGICAL_ERROR = 1102;

	/**
	 * An invalid reflection parent was provided.
	 *
	 * @var int
	 */
	const INVALID_PARENT = 1103;

	/**
	 * Minimal number of source code lines around the token.
	 *
	 * @var int
	 */
	const SOURCE_LINES_AROUND = 5;

}
